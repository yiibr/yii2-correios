<?php

namespace yiibr\correios;

use Yii;
use yii\web\Response;
use yii\web\NotFoundHttpException;


class CepAction extends \yii\base\Action
{
    const URL_CORREIOS = 'http://www.buscacep.correios.com.br/servicos/dnec/consultaEnderecoAction.do';

    /**
     * Searches address by cep or location
     * @param string $q query
     * @return array cep data
     */
    public function run($q)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $q ? $this->search($q) : [];
    }

    /**
     * Processes html content, returning cep data
     * @param string $q query
     * @return array cep data
     */
    protected function search($q)
    {
        $result = [];
        $fields = http_build_query([
            'relaxation' => $q,
            'semelhante' => 'S',
            'TipoCep' => 'ALL',
            'Metodo' => 'listaLogradouro',
            'TipoConsulta' => 'relaxation'
        ]);

        $curl = curl_init(self::URL_CORREIOS);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);

        $response = curl_exec($curl);
        curl_close($curl);

        if (preg_match_all('/\<td.*?\>(.*?)\<\/td\>/i', utf8_encode($response), $matches)){
            $i = 0;
            $address = [];
            foreach ($matches[1] as $value) {
                switch ($i) {
                    case 0:
                        $address['location'] = $value;
                        break;
                    case 1:
                        $address['district'] = $value;
                        break;
                    case 2:
                        $address['city'] = $value;
                        break;
                    case 3:
                        $address['state'] = $value;
                        break;
                    default:
                        $address['cep'] = $value;
                        $result[] = $address;
                        $address = [];
                        $i = -1;
                }
                $i++;
            }
        }
        return $result;
    }
}