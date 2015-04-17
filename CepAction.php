<?php

namespace yiibr\correios;

use Yii;
use yii\web\Response;
use DOMDocument;


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
            'semelhante' => 'N',
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

        static $pattern = '/<table border="0" cellspacing="1" cellpadding="5" bgcolor="gray">(.*?)<\/table>/is';
        if (preg_match($pattern, $response, $matches)) {
            $html = new DOMDocument();
            if ($html->loadHTML($matches[0])) {
                $rows = $html->getElementsByTagName('tr');
                foreach ($rows as $tr) {
                    $cols = $tr->getElementsByTagName('td');
                    $result[] = [
                        'location' => $cols->item(0)->nodeValue,
                        'district' => $cols->item(1)->nodeValue,
                        'city' => $cols->item(2)->nodeValue,
                        'state' => $cols->item(3)->nodeValue,
                        'cep' => $cols->item(4)->nodeValue,
                    ];
                }
            }
        }
        return $result;
    }

}