
<?php

use yii\helpers\Html;
?>
<div id="filelist" class="plupload-preview">

</div>

<?php if ($autoUpload): ?>
	<?= Html::a($browseLabel, '#', $browseOptions) ?>
<?php else: ?>
	<?= Html::a($browseLabel, '#', $browseOptions) ?>
	<?= Html::a($uploadLabel, '#', $uploadOptions) ?>
<?php endif; ?>

<div id="<?= $errorContainer ?>" class="plupload-console"></div>