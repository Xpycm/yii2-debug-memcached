<?php
use yii\helpers\Html;
use salopot\debug\memcached\panels\MemcachedPanel;

/* @var MemcachedPanel $panel */

$stateLabels = [
    MemcachedPanel::STATE_GOOD => 'label-success',
    MemcachedPanel::STATE_NORMAL => '',
    MemcachedPanel::STATE_WARNING => 'label-warning',
    MemcachedPanel::STATE_ERROR => 'label-important',
];
?>
<div class="yii-debug-toolbar__block">
    <?=Html::a('Memcached '
        .Html::tag('span', "{$panel->formatSize($summary['bytes'])} ({$panel->formatPercent($summary['percent_memory_usage'])})", [
            'class' => 'label '.$stateLabels[$summary['usageState']]
        ])
        .($summary['count_servers'] > $summary['count_work'] ? ' Servers: '.Html::tag('span', "{$summary['count_work']}/{$summary['count_servers']}", [
            'class' => 'label label-important'
        ]) : ''),
        $panel->getUrl(), [
            'title' => "Memory: {$panel->formatSize($summary['free_bytes'])}/{$panel->formatSize($summary['limit_maxbytes'])}\nServers: {$summary['count_work']}/{$summary['count_servers']}"
        ]);  ?>
</div>
