<?php

namespace yiibr\correios;

use DOMDocument;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;


class CepAction extends \yii\base\Action

{
    const URL_CORREIOS = 'http://www.buscacep.correios.com.br/sistemas/buscacep/resultadoBuscaCepEndereco.cfm';

    /**
     * @var array data sent in request
     */
    public $formData = [];

    /**
     * @var string name of query parameter
     */
    public $queryParam = '_cep';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->formData = array_merge([
            'semelhante' => 'N',
            'tipoCEP' => 'ALL',
            'tipoConsulta' => 'relaxation'
        ], $this->formData);
    }

    /**
     * Searches address by cep or location
     * @return array cep data
     * @throws NotFoundHttpException
     */
    public function run()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = Yii::$app->request->get($this->queryParam);
        $result = $this->search($query);

        if (!$result) {
            throw new NotFoundHttpException("Endereço não encontrado");
        }

        return $result;
    }

    /**
     * Processes html content, returning cep data
     * @param string $q query
     * @return array cep data
     */
    protected function search($q)
    {
        $result = [];
        $fields = array_merge([$this->formData['tipoConsulta'] => $q], $this->formData);
        static $pattern = '/<table class="tmptabela">(.*?)<\/table>/is',
        $keys = [
            'location',
            'district',
            'city',
            'state',
            'cep',
        ];

        $curl = curl_init(self::URL_CORREIOS);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($fields));

        $response = curl_exec($curl);
        curl_close($curl);

        if (preg_match($pattern, $response, $matches)) {
            $html = new DOMDocument();
            if ($html->loadHTML($matches[0])) {
                $rows = $html->getElementsByTagName('tr');

                $header = $rows->item(0);
                $header->parentNode->removeChild($header);

                foreach ($rows as $tr) {
                    $values = preg_replace("/\r+/", "\r", trim($tr->nodeValue));
                    $values = preg_split("/\r/", $values);

                    array_walk($values, function (&$value, $key) {
                        $value = trim($value, " \t\n\r\0\x0B\xC2\xA0");
                        if ($key === 2) $value = explode('/', $value);
                    });

                    $this->flatten($values);

                    $result[] = array_combine($keys, $values);
                }
            }
        }
        return $result;
    }

    private function flatten(&$array)
    {
        foreach ($array as $i => $items) {
            if (gettype($items) === 'array') {
                unset($array[$i]);
                foreach ($items as $key => $value)
                    array_splice($array, $i++, 0, $value);
            }
        }
    }
}
