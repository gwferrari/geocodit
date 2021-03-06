<?php
/**
 * 
 * @author Enrico Fagnoni <enrico@linkeddata.center>
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

namespace Geocodit\Gateway;

class GwHelpers {
	
	/**
	 * PHP equivalent to UCASE(REPLACE(?id,"[^a-zA-Z0-9]",""))
	 */
	public static function encodeForUri($id){
		return strtoupper( preg_replace('/[^a-zA-Z0-9]/', '', $id));
	}

	public static  function toFloat($str) {            
        if(strstr($str, ',')) {
            // A comma exists, that makes it easy, cos we assume it separates the decimal part.
            $str = str_replace('.', '', $str);    // Erase thousand seps
            $str = str_replace(',', '.', $str);    // Convert , to . for floatval command  
        }
		return floatval($str);
    }
	
    public static  function quote($str,$langOrType='') {
    	// set to utf8
		$str = utf8_encode($str);
		
    	// change  all backslash into slash
    	$str =  str_replace('\\', '/', utf8_encode($str));
		
		//escape double quotes in string
		$str = str_replace('"','\"',$str);
		
		// return quoted string 
		return  '"'.$str.'"'.$langOrType;
    }

	
} //END