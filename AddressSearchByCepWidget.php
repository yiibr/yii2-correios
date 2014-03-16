<?php
/**
 * @link https://github.com/yiibr/yii2-correios
 * @copyright Copyright (c) 2013 Wanderson Bragança
 * @license https://github.com/yiibr/yii2-correios/blob/master/LICENSE
 */

namespace yiibr\correios;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\view;
use yii\base\InvalidConfigException;

class AddressSearchByCepWidget extends Widget
{
    /**
     * ID html element to capture the click event
     * @var string $target
     */
    public $target = null;
    /**
     * Model class
     * @var object $model
     */
    public $model;
    /**
     * field postal code
     * @var string $attribute
     */
    public $attribute;
    /**
     * Array to map the attributes of the model and Widget
     * ~~~
     *  array(
     *      'location' => 'field_location',
     *      'district' => 'field_district',
     *      'city' => 'field_city',
     *      'state' => 'field_state',
     *  );
     * ~~~
     * @var array $config
     */
    public $config = array();
    /**
     * Action
     * @var string $action
     */
    public $action = '';
    /**
     * Map fields
     * @var array $_fieldsMap
     */
    private $_fieldsMap = array(
        'location'=>'',
        'district'=>'',
        'city'=>'',
        'state'=>'',
        'result'=>0,
    );


    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $_fieldsMap = array();
        foreach( $this->config as $field => $attributeName ){
            if( isset($this->_fieldsMap[$field]) && $field != 'result' && $field != 'restul_text'){
                $_fieldsMap[$field] = Html::getInputId($this->model, $attributeName);
            } else {
                throw new InvalidConfigException('Invalid parameter.');
            }
        }
        if( $this->target !== null )
            $this->registerJS($_fieldsMap);
    }

    public static function Widget($config = [])
    {
        $config['class'] = get_called_class();
        $widget = Yii::createObject($config);
        $widget->run();
    }

    /**
     * Register script
     * @param array $_fieldsMap
     */
    protected function registerJS($_fieldsMap)
    {
        $_fieldsMap     = Json::encode($_fieldsMap);
        $postcodeID   = Html::getInputId($this->model, $this->attribute);
        $action         =  \Yii::$app->urlManager->createAbsoluteUrl($this->action);
        $targetErrorMsg = 'field-' . $postcodeID;

        $js = <<<EOF
jQuery("{$this->target}").on("click", function(){
    var btn = jQuery(this);
    btn.button('loading');
    var _fieldsMap = {$_fieldsMap};
    jQuery.each(_fieldsMap, function(key, val) {
        jQuery('#' + val).attr("disabled","true");
    });
    jQuery.ajax({
        dataType: "json",
        url: "{$action}",
        data: {'postcode':jQuery("#{$postcodeID}").val()},
        success: function(json){
            $.each(_fieldsMap, function(key, val) {
                jQuery('#' + val).removeAttr("disabled","true");
                jQuery('#' + val).val(unescape(json[key]));
            });
            if( json['result'] == '0'){
                jQuery(".{$targetErrorMsg}").removeClass("has-success").addClass("has-error");
                jQuery(".{$targetErrorMsg} .help-block").show().html(json['result_text']); 
            }
            btn.button('reset');
        }
    });
    return false;
});
EOF;
        $view = $this->getView();
        $view->registerJs($js, \yii\web\view::POS_END);
    }
}
