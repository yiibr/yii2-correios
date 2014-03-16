<?php 

/**
 * @link https://github.com/yiibr/yii2-correios
 * @copyright Copyright (c) 2013 Wanderson Bragança
 * @license https://github.com/yiibr/yii2-correios/blob/master/LICENSE
 */

namespace yiibr\correios\actions;

use Yii;
use yiibr\correios\AddressSearchByCep;
use yii\base\Action;
use yii\web\Response;

class AddressSearchByCepAction extends Action
{
    public function run()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $postcode = isset($_GET['postcode']) ? $_GET['postcode'] : '';
        return AddressSearchByCep::getData(['postcode' => $postcode]);
    }
}
