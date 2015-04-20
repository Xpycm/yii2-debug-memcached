<?php
use yii\helpers\Html;
use yii\bootstrap\Tabs;

/* @var \salopot\debug\memcached\panels\MemcachedPanel $panel */
/* @var array $summary */
/* @var array $stats */
?>
<h1>Memcached statistics</h1>
<p>
    Used memory: <b><?= $panel->formatSize($summary['bytes']); ?></b>;
    Percent usage: <b><?= $panel->formatPercent($summary['percent_memory_usage']); ?></b>;
    Total memory: <b><?= $panel->formatSize($summary['limit_maxbytes']); ?></b>;
    Servers: <b><?= $summary['count_work']; ?>/<?= $summary['count_servers']; ?></b>.
</p>

<?= Tabs::widget([
    'encodeLabels' => false,
    'items' => array_map(function($server, $stat){
        return [
            'label' => Html::encode($server).' '.Html::tag('span', $stat !== false ? 'Work' : 'OffLine', [
                    'class' => 'label '.($stat !== false ? 'label-success' : 'label-warning')
                ]),
            'content' => $this->render('@salopot/debug/memcached/views/table', ['values' => $stat])
        ];
    }, array_keys($stats), $stats),
]); ?>