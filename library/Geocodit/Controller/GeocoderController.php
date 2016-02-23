<?php
/**
 * @author Enrico Fagnoni <e.fagnoni@e-artspace.com>
 * @copyright (c) 2016 LinkedData.Center. All right reserved.
 * @package geocodit
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 */
namespace Geocodit\Controller;

use BOTK\Context\Context;				// get config vars and other inputs

class GeocoderController extends AbstractController {
	protected $PROFILES = array(
		'cost' 		=> array('openstreetmap','google_maps','bing_maps'),
		'quality' 	=> array('bing_maps','google_maps','openstreetmap'),
		'open'		=> array('openstreetmap'),
	);

	public  function VALID_PENALITY() {
		return  array(
			'filter'    => FILTER_VALIDATE_INT,
			'flags'  	=> FILTER_REQUIRE_SCALAR,
			'options'   => array("min_range"=>0,"ax_range"=>2)
		);
	} 

    public  function VALID_PROFILE(){
     	$enumProfiles = implode('|', array_keys($this->PROFILES));	
        return  array(
                'filter'    => FILTER_VALIDATE_REGEXP,
                'options'   => array('regexp' => "/^($enumProfiles)$/")
        );
    }
	 
	 
	public function get() {
		
		$context = Context::factory();
		// get default parameters from config
		$config	 = $context->ns('geocodit');
		$defaultProfile 	= $config->getValue( 'profile', 		'cost', 								$this->VALID_PROFILE());
		$defaultAddress		= $config->getValue( 'defaultAddress', 	'Via Montefiori 13, 23825 Esino Lario', FILTER_SANITIZE_STRING);
		$penality			= $config->getValue( 'penality', 		2,  									$this->VALID_PENALITY());
		
		// get input patrameters from URL quesry string
		$input = $context->ns(INPUT_GET);
		$query				= $input->getValue( 'q', 				$defaultAddress, 						FILTER_SANITIZE_STRING);
		$profile			= $input->getValue( 'profile', 			$defaultProfile, 						self::VALID_PROFILE());

		$geocoder = new \Geocoder\ProviderAggregator();
		$adapter  = new \Ivory\HttpAdapter\CurlHttpAdapter();
		
		// chain all supported providers
		$chain = new \Geocoder\Provider\Chain(array());	
		foreach($this->PROFILES[$profile] as $providerName) {
			$provider = $this->geocoderFactory($adapter, trim($providerName));
			$chain->add($provider);
		}
		
		$geocoder->registerProvider($chain);
		
		// Call toponym resolution providers
		try {
			$toponymResolution = $geocoder
				->limit(1)
				->geocode($query)
				->first();
			$formatter = new \Geocoder\Formatter\StringFormatter();			
			$canonicAddress = $formatter->format($toponymResolution, "%S %n, %z %L");	
		} catch (\Exception $e) {
			$toponymResolution = null;
			$canonicAddress = $query;
		}

		// apply penality (just to avoid abuse, set to 0 in config fil to disable)
		if($penality>0) {usleep($penality*1000000);}

		// call geocodit specialized provider. This looks at linked data to guess a better address
		$geocodit = $this->geocoderFactory($adapter, 'geocodit');
		try {
			$geocoditAddress = $geocodit
				->limit(1)
				->geocode($canonicAddress)
				->first();	
		} catch (\Exception $e) {
			$geocoditAddress =$toponymResolution ; //fallback to toponymResolution
		}		
		
		if (is_null($geocoditAddress)) {
			throw new \Exception("Not found", 404);
		}
		
		
		return  $this->stateTransfer($geocoditAddress);
    }				
		
} // END