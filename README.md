yii2-correios
=============

This extension allows automatic completion and search Brazilian address.


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
        'addressSearch' => 'yiibr\correios\CepAction'
        ...
    ];
}
```


### How to use

On your view file.

```php

<?php
use yiibr\correios\CepInput;
?>

...

<?php CepInput::widget([
    'action' => ['addressSearch'],
    'fields' => [
        'location' => 'location_input_id',
        'district' => 'district_input_id',
        'city' => 'city_input_id',
        'state' => 'state_input_id'
    ],
]); ?>

```

```php

// Example:

<?= $form->field($model, 'cep',)->widget('yiibr\correios\CepInput', [
    'action' => ['addressSearch'],
    'fields' => [
        'location' => 'address-location',
        'district' => 'address-district',
        'city' => 'address-city',
        'state' => 'address-state',
    ],
]) ?>

<?= $form->field($model, 'location')->textInput() ?>
<?= $form->field($model, 'district')->textInput() ?>
<?= $form->field($model, 'city')->textInput() ?>
<?= $form->field($model, 'state')->textInput() ?>
```
