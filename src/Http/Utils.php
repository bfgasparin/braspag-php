<?php

namespace Braspag\Http;

use Braspag\Http\Exception\ApiException;

/**
 * Methods used across services
 *
 * @version 1.0
 */
class Utils
{
    public static function getResponseValue($from, $propName){
        return property_exists($from, $propName) ? $from->$propName : null;
    }
    
    public static function handleApiError($response)
    {
        if($response->code !== HttpStatus::BadRequest){
            return;
        }

        $errors = $response->body;
        if (is_array($errors)){
            $error = $errors[0];
        }else{
            $error = $errors;

        }
        throw new ApiException($error->Message, isset($e->Code) ? $e->Code: null);
    }

    /**     
     * Debug Function
     * @param Sale $debug,$title 
     * @return standardoutput
     * @autor interatia
     */
    public static function debug($debug,$title="Debug:")
    {
        echo "<hr/>";
        echo "<h2>Start: $title</h2>";
        echo '<textarea cols="100" rows="50">';    
        print_r($debug);
        echo "</textarea>";
        echo "<h2>End: $title</h2>";
        echo "<hr/>";
    } 
}
