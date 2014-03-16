yii2-correios
=============

This extension allows automatic completion of Brazilian Address by postal code.


### Installation


Add to the ```require``` section of your `composer.json` file:

```
"yiibr/yii2-correios": "*"
```


### Configuration

On your controller.

```php
public function actions()
{
    return [
        ...
        'addressSearch' => 'yiibr\correios\actions\AddressSearchByCepAction'
        ...
    ];
}
```


### How to use

On your view file.

```php
<?php
use yiibr\correios\AddressSearchByCepWidget;
?>

...

<?php AddressSearchByCepWidget::widget([
    'target' => '#btnSearchAddress',
    'model' => $model,
    'attribute' => 'postcode',
    'action' => 'yourController/addressSearch',
    'config' => [
        'location' => 'address',
        'district' => 'district',
        'city' => 'city',
        'state' => 'state',
    ]
]); ?>

```

```php

// Example:

<div class="row">
    <div class="col-lg-5">
        <?php $form = ActiveForm::begin(['id' => 'address-form']); ?>
            <?= $form->field($model, 'postcode') ?>
            <?= Html::Button('Search Address', [
                'class' => 'btn btn-primary', 
                'id' => 'btnSearchAddress',
                'data-loading-text' => 'Loadding...',
            ]) ?>
            <?= $form->field($model, 'address') ?>
            <?= $form->field($model, 'district') ?>
            <?= $form->field($model, 'city') ?>
            <?= $form->field($model, 'state') ?>
        <?php ActiveForm::end(); ?>
    </div>
</div>
```
