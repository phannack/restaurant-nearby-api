<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class ApiController extends AbstractActionController
{


    public function __construct()
    {
        $this->key = 'AIzaSyBNIfIxsdaPM-5A6fUADEUpI9dugy8TjB8';
    }



    public function nearbyAction()
    {



        $location = $this->params()->fromRoute('location', -1);

        //Get Lat Long.
        $url = 'https://maps.googleapis.com/maps/api/place/findplacefromtext/json?inputtype=textquery&input=' . urlencode($location) . '&fields=geometry&key='.$this->key;

        // append the header putting the secret key and hash
        $request_headers = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($ch);

        if (curl_errno($ch))
        {

            $response = new JsonModel(array(
                'status' => false,
                'message' => "Error: ".curl_error($ch)
            ));
        }
        else
        {

            $body = json_decode($data, TRUE);


            if( $body['status'] == 'OK' && count( $body['candidates'] ) > 0 ) {
                $position = ['lat' => $body['candidates'][0]['geometry']['location']['lat'], 'lng' => $body['candidates'][0]['geometry']['location']['lng']];
                curl_close($ch);


                $restaurant = $this->getGoogleMapApiNeaby($position);

                $response = new JsonModel(array(
                    'location' => $location,
                    'position' => $position,
                    'restaurant' => $restaurant,
                    'status' => true,
                ));

            }else{
                $response = new JsonModel(array(
                    'status' => false,
                    'message' => "Cannot get this position "
                ));
            }


        }
        return $response;


    }


    //Get Restaurant from location.
    public function getGoogleMapApiNeaby($position)
    {

        $url = 'https://maps.googleapis.com/maps/api/place/nearbysearch/json?location='. $position['lat'] .','.$position['lng'] .'&radius=500&type=restaurant&key='.$this->key;

        $request_headers = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($ch);

        if (curl_errno($ch))
        {
            print "Error: " . curl_error($ch);
        }
        else
        {

            $restaurant = json_decode($data, TRUE);

            curl_close($ch);
            return $restaurant['results'];
        }

    }
}
