<?php
use yii\helpers\Html;

/* @var $values array */
?>
<?php if (empty($values)): ?>
    <p>Server is unavailable!</p>
<?php else:	?>
    <div class="table-responsive">
        <table class="table table-condensed table-bordered table-striped table-hover request-table" style="table-layout: fixed;">
            <thead>
            <tr>
                <th>Name</th>
                <th>Value</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($values as $name => $value): ?>
                <tr>
                    <th><?= Html::encode($name) ?></th>
                    <td><?= Html::encode($value) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>