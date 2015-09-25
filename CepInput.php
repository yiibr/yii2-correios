<?php

namespace yiibr\correios;

use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\InputWidget;

class CepInput extends InputWidget
{

    /**
     * @var array|string $action the form action URL. This parameter will be processed by [[Url::to()]].
     */
    public $action = '';

    /**
     * @var string the css search icon class
     */
    public $searchIcon = 'glyphicon glyphicon-search';

    /**
     * @var array $fields ID of html elements that will receive result of search
     * ```php
     * [
     *     'location' => 'location_input_id',
     *     'district' => 'district_input_id',
     *     'city' => 'city_input_id',
     *     'state' => 'state_input_id',
     * ]
     * ```
     */
    public $fields = [
        'location' => '',
        'district' => '',
        'city' => '',
        'state' => '',
    ];

    /**
     * @var string name of query parameter
     */
    public $queryParam = '__q';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        CepAsset::register($this->getView());
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        Html::addCssClass($this->options, 'form-control');

        if ($this->hasModel()) {
            $input = Html::activeTextInput($this->model, $this->attribute, $this->options);
        } else {
            $input = Html::textInput($this->name, $this->value, $this->options);
        }

        $this->renderSearch($input, $this->id);
        $this->renderModal();
        $this->registerJs();
    }

    /**
     * Renders search input box
     * @param string $input input tag
     * @param string $id
     */
    protected function renderSearch($input, $id = null)
    {
        echo Html::beginTag("div", ['class' => 'input-group', 'id' => $id]);
        echo $input;
        echo Html::beginTag("span", ['class' => 'input-group-btn']);
        echo Html::beginTag("a", ['class' => 'btn btn-default']);
        echo Html::tag("i", null, ['class' => $this->searchIcon]);
        echo Html::endTag("a");
        echo Html::endTag("span");
        echo Html::endTag("div");
    }

    /**
     * Renders a modal window
     */
    protected function renderModal()
    {
        echo Html::beginTag('div', ['class' => 'fade modal', 'role' => 'dialog', 'tabindex' => '-1']);
        echo Html::beginTag('div', ['class' => 'modal-dialog modal-lg']);
        echo Html::beginTag('div', ['class' => 'modal-content']);

        echo Html::beginTag('div', ['class' => 'modal-header']);
        echo Html::button('&times;', ['class' => 'close', 'data-dismiss' => 'modal', 'aria-hidden' => true]);
        echo "CEP";
        echo Html::endTag('div');

        echo Html::beginTag('div', ['class' => 'modal-body']);
        echo Html::label('Endereço');

        $input = Html::textInput(null, null, ['class' => 'form-control']);
        $this->renderSearch($input);
        $this->renderGrid();

        echo Html::endTag('div');

        echo Html::beginTag('div', ['class' => 'modal-footer']);
        echo Html::button('Fechar', ['class' => 'btn btn-default', 'data-dismiss' => 'modal', 'aria-hidden' => true]);
        echo Html::endTag('div');

        echo Html::endTag('div');
        echo Html::endTag('div');
        echo Html::endTag('div');
    }

    /**
     * Renders address grid
     */
    protected function renderGrid()
    {
        echo Html::Tag('div', null, ['class' => 'separator bottom']);
        echo Html::beginTag('div', ['class' => 'row-fluid']);
        echo Html::beginTag('table', ['class' => 'table table-bordered table-striped']);

        echo Html::beginTag('thead');
        echo Html::beginTag('tr');
        echo Html::Tag('th', "CEP");
        echo Html::Tag('th', "Endereço");
        echo Html::Tag('th', "Bairro");
        echo Html::Tag('th', "Cidade");
        echo Html::Tag('th', "UF");
        echo Html::endTag('tr');
        echo Html::endTag('thead');

        echo Html::beginTag('tbody');
        echo Html::endTag('tbody');

        echo Html::endTag('table');
        echo Html::endTag('div');
    }

    protected function registerJs()
    {
        $id = $this->options['id'];
        $options = Json::encode([
            'action' => Url::to($this->action),
            'fields' => $this->fields,
            'queryParam' => $this->queryParam
        ]);

        $this->getView()->registerJs("jQuery('#{$id}').cep($options);");
    }

} 
