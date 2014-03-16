<?php
/**
 * @link https://github.com/yiibr/yii2-correios
 * @copyright Copyright (c) 2013 Wanderson Bragança
 * @license https://github.com/yiibr/yii2-correios/blob/master/LICENSE
 */

namespace yiibr\correios;

use Yii;
use yii\base\Component;
use yii\helpers\Html;
use yiibr\correios\BaseCorreios;


class AddressSearchByCep extends Component
{

    const URL_CORREIOS_MOBILE = 'http://m.correios.com.br/movel/buscaCepConfirma.do?tipoCep=&cepTemp=&metodo=buscarCep&cepEntrada=';
    const URL_CORREIOS = 'http://www.buscacep.correios.com.br/servicos/dnec/consultaLogradouroAction.do?Metodo=listaLogradouro&TipoConsulta=cep&CEP=';

    /**
     * @var string $postcode
     */
    public $postcode;
    /**
     * @var array $fieldsMap
     */
    private $_fields = [
        'location' => '',
        'district' => '',
        'city' => '',
        'state' => '',
        'result' => 0,
    ];

    public function init()
    {
        parent::init();
        $this->_fields['result_text'] = 'Address not found.';
    }

    public function run()
    {
        $postcode = str_replace('-', '', $this->postcode);
        $output = $this->_fields;

        if( empty($postcode) || strlen($postcode) != 8 ){
            $output['result_text'] = 'Invalid postcode.';
            $output['result'] = 0; 
            return $output;
        }else{
            $address = $this->getAddress($postcode);
            return array_merge($output, $address);
        }
        return $output;
    }

    public static function getData($config = [])
    {
        $config['class'] = get_called_class();
        $widget = Yii::createObject($config);
        return $widget->run();
    }

    protected function getHTMLCorreios($url)
    {
        return file_get_contents($url);
    }

    protected function getAddress($postcode)
    {
        $address = $this->getAddressOpt1($postcode);

        if($address === 'WEBPAGE_NOT_AVAILABLE'){
            $address = $this->getAddressOpt2($postcode);
        }
        return $address;
    }

    /**
     * @param string $postcode
     * @return array|string
     */
    protected function getAddressOpt1($postcode)
    {
        $html = $this->getHTMLCorreios(self::URL_CORREIOS_MOBILE . $postcode);
        $html = preg_replace('/\n|\r|\t/', '', utf8_encode($html));
        $address = [];

        if (strpos($html, 'Tente novamente mais tarde.') !== false ){
            return 'WEBPAGE_NOT_AVAILABLE';
        }

        preg_match_all('/<span class=\"respostadestaque\">(.*?)<\/span>/', $html, $itens);

        if ( isset($itens[1]) ) {

            if (count($itens[1]) >= 4) {
                foreach ($itens[1] as $i=>$item) {
                    switch ($i) {
                        case 0:
                            $address['location'] = trim($item);
                            break;
                        case 1:
                            $address['district'] = trim($item);
                            break;
                        case 2:
                            list($city, $state) = array_map('trim', explode('/', $item));
                            $address['city'] = $city;
                            $address['state'] = $state;
                            break;
                    }
                }
            } elseif (count($itens[1]) === 2) {
                foreach ($itens[1] as $i=>$item) {
                    switch ($i) {
                        case 0:
                            list($city, $state) = array_map('trim', explode('/', $item));
                            $address['city'] = $city;
                            $address['state'] = $state;
                            break;
                    }
                }
            }

            if (!empty($address)) {
                $address['result_text'] = 'Address found.';
                $address['result'] = 1;
            }

            return $address;
        }

        return [];
    }

    /**
     * @param string $postcode
     * @return array
     */
    protected function getAddressOpt2($postcode)
    {
        $html = $this->getHTMLCorreios(self::URL_CORREIOS . $postcode);
        $html = preg_replace('/\n|\r|\t/', '', $html);

        $findBy = '<tr bgcolor="#ECF3F6" onclick="javascript:detalharCep(';
        $nPos = strpos($html, $findBy);
        $cleanHTML = '';
        $address = $this->_fields;
        if($nPos){
            $cleanHTML  = substr($html, $nPos);
            $nPos       = strpos($cleanHTML, '</tr>');
            $cleanHTML  = substr($cleanHTML, 0, $nPos+5);
            $doc = new \DOMDocument(); 
            if( $doc->loadHTML($cleanHTML) ) {
                $tagData = $doc->getElementsByTagName('td');
                if( $tagData->length > 0 ){
                    $address['location']    = $tagData->item(0)->nodeValue;
                    $address['district']    = $tagData->item(1)->nodeValue;
                    $address['city']        = $tagData->item(2)->nodeValue;
                    $address['state']       = $tagData->item(3)->nodeValue;
                    $address['result']      = 1;
                    $address['result_text'] = 'Address found.';
                }
            }
        }
        return $address;
    }
}
