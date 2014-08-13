<?php

namespace yiibr\correios;

use Yii;
use yii\web\Response;
use yii\web\NotFoundHttpException;


class CepAction extends \yii\base\Action
{
    const URL_CORREIOS_MOBILE = 'http://m.correios.com.br/movel/buscaCepConfirma.do?tipoCep=&cepTemp=&metodo=buscarCep&cepEntrada=';

    /**
     * Searches address by cep or location
     * @param string $q query
     * @return array cep data
     */
    public function run($q)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!$q) {
            return [];
        }
        return $this->searchMobile($q);
    }

    /**
     * Processes html content, returning cep data
     * @param string $q query
     * @return array cep data
     * @throws \yii\web\NotFoundHttpException
     */
    public function searchMobile($q)
    {
        $result = [];
        $html = file_get_contents(self::URL_CORREIOS_MOBILE . $q);
        $html = preg_replace('/\n|\r|\t/', '', utf8_encode($html));

        if (preg_match('/class=\"erro\"/', $html)){
            throw new NotFoundHttpException('Address not found');
        }

        preg_match_all('/<div class=\"caixacampo\w+\">.*?<\/div>/', $html, $rows);

        foreach ($rows as $r) {
            foreach ($r as $content) {
                preg_match_all('/<span class=\"respostadestaque\">(.*?)<\/span>/', $content, $matches);
                $data = isset($matches[1]) ? $matches[1] : null;

                if ($data) {
                    $address = [];

                    if (count($data) >= 4) {
                        foreach ($data as $i => $item) {
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
                                case 3:
                                    $address['cep'] = trim($item);
                                    break;
                            }
                        }
                    } else if (count($data) === 2) {
                        foreach ($data as $i => $item) {
                            switch ($i) {
                                case 0:
                                    list($city, $state) = array_map('trim', explode('/', $item));
                                    $address['city'] = $city;
                                    $address['state'] = $state;
                                    break;
                                case 1:
                                    $address['cep'] = trim($item);
                                    break;
                            }
                        }
                    }
                    $result[] = $address;
                }
            }
        }
        return $result;
    }
} 