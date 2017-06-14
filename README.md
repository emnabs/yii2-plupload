# Yii2 Plupload Widget

yii2-plupload is a widget based plupload solution for Yii2. It is released under the BSD 3-Clause license.

[![Latest Stable Version](https://poser.pugx.org/emnabs/yii2-plupload/v/stable.png)](https://packagist.org/packages/emnabs/yii2-plupload)
[![Total Downloads](https://poser.pugx.org/emnabs/yii2-plupload/downloads.png)](https://packagist.org/packages/emnabs/yii2-plupload)
[![License](https://poser.pugx.org/emnabs/yii2-plupload/license.png)](https://packagist.org/packages/emnabs/yii2-plupload)


## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist emnabs/yii2-plupload "*"
```

or add

```json
"emnabs/yii2-plupload": "*"
```

to the require section of your composer.json.

## Usage

To use this widget, you have to add the code in your viewer page:

Usage With ActiveForm and model

```
use emhome\plupload\Plupload;

echo $form->field($model, 'thumb')->widget(Plupload::classname(), [
    'url' => ['upload'],
    //'wrapperOptions' => ['width' => 200, 'height' => 200],
    //'resize' => ['width' => 200, 'height' => 200],
    'autoUpload' => true,
    'options' => [
        'filters' => [
            'mime_types' => [
                [
                    'title' => "Image files",
                    'extensions' => "jpg,gif,png"
                ],
            ]
        ],
    ],
]);
```

Usage Without ActiveForm model

```
use emhome\plupload\Plupload;

Plupload::widget([
    'url' => ['upload'],
    'browseLabel' => '上传文件',
    'autoUpload' => true,
    'errorContainer' => 'errorUpload',
    'options' => [
        'filters' => [
            'max_file_size' => '20kb',
            'mime_types' => [
                [
                    'title' => "Image files",
                    'extensions' => "jpg,gif,png"
                ],
            ]
        ],
    ],
    'events' => [],
]);
```

Usage actions with PluploadAction

```php
public function actions()
{
    return [
	...
        'plupload' => [
	    'class' => 'emhome\plupload\PluploadAction',
	    'onComplete' => function($file, $params) {
	        //上传完成后操作
		...
	        return [
	            'file' => $file,
	            'params' => $params
	        ];
	    },
	],
	...
    ];
}
```


## License

**yii2-plupload** is released under the `BSD 3-Clause` License. See the bundled `LICENSE.md` for details.


## Plupload

Copyright 2016, [Ephox](http://www.ephox.com/)  
Released under [GPLv2 License](https://github.com/moxiecode/plupload/blob/master/license.txt)