<?php
/**
 * Created by PhpStorm.
 * User: salopot
 * Date: 15.04.2015
 * Time: 12:14
 */

namespace salopot\debug\memcached\panels;

use Yii;
use yii\base\NotSupportedException;
use yii\caching\MemCache;
use yii\debug\Panel;

class MemcachedPanel extends Panel {

    const STATE_GOOD = 0;
    const STATE_NORMAL = 1;
    const STATE_WARNING = 2;
    const STATE_ERROR = 3;


    /**
     * The name of Yii component cache
     */
    public $componentName = 'cache';
    public $useFormatter = true;

    public $memoryUsageStates = [
        -1 => self::STATE_ERROR, //not used
        0 => self::STATE_WARNING, //very small usage
        5 => self::STATE_NORMAL, //used
        50 => self::STATE_GOOD, //
        95 => self::STATE_WARNING, //insufficiently
        99 => self::STATE_ERROR //almost over
    ];

    protected $instance;

    public function init()
    {
        parent::init();

        //Try get instance from cache
        $cache = Yii::$app->get($this->componentName, false);
        if ($cache !== null && $cache instanceof MemCache) {
            $this->instance = $cache->getMemcache();
        }
    }

    protected function getIsAvailable() {
        return $this->instance !== null;
    }


    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Memcached';
    }

    /**
     * @inheritdoc
     */
    public function getSummary()
    {
        if ($this->getIsAvailable()) {
            return Yii::$app->view->render('@salopot/debug/memcached/views/summary', [
                'panel' => $this,
                'summary' => $this->data['summary'],
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function getDetail()
    {
        if ($this->getIsAvailable()) {
            $stats = $this->data['stats'];
            foreach($stats as $server => &$stat) {
                if ($stat !== false) {
                    foreach($stat as $name => &$value) {
                        switch ($name) {
                            case 'bytes':
                            case 'free_bytes':
                            case 'limit_maxbytes':
                            case 'bytes_read':
                            case 'bytes_written':
                                $value = $this->formatSize($value);
                                break;
                            case 'percent_memory_usage':
                                $value = $this->formatPercent($value);
                                break;
                            case 'uptime':
                                $value = $this->formatExecTime($value);
                                break;
                            case 'time':
                                $value = $this->formatDateTime($value);
                                break;
                        }
                    }
                }
            }

            return Yii::$app->view->render('@salopot/debug/memcached/views/detail', [
                'panel' => $this,
                'summary' => $this->data['summary'],
                'stats' => $stats,
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        if ($this->getIsAvailable()) {
            if ($this->instance instanceof \Memcached) {
                $stats = $this->instance->getStats();
            } elseif ($this->instance instanceof \Memcache) {
                $stats = $this->instance->getExtendedStats();
            } else {
                throw new NotSupportedException('Not supported memcached client: '.get_class($this->instance));
            }

            //Aggregate stats
            $bytes = $uptime = $limit_maxbytes = $count_work = 0;
            foreach($stats as $server => &$stat) {
                if ($stat !== false) {
                    //summary
                    $bytes += $stat['bytes'];
                    $limit_maxbytes += $stat['limit_maxbytes'];
                    $uptime = max($uptime, $stat['uptime']);
                    $count_work++;

                    //server
                    $stat['free_bytes'] = $stat['limit_maxbytes'] - $stat['bytes'];
                    $stat['percent_memory_usage'] = $stat['bytes'] / $stat['limit_maxbytes'];
                }
            }
            $count_severs = count($stats);
            $percent_memory_usage = $bytes / $limit_maxbytes;

            //calc cache state
            foreach($this->memoryUsageStates as $percent => $state) {
                if ($percent_memory_usage*100 > $percent) $usageState = $state;
            }

            return array(
                'summary' => array(
                    'bytes' => $bytes,
                    'free_bytes' => $limit_maxbytes - $bytes,
                    'limit_maxbytes' => $limit_maxbytes,
                    'percent_memory_usage' => $percent_memory_usage,
                    'uptime' => $uptime,
                    'count_servers' => $count_severs,
                    'count_work' => $count_work,
                    'usageState' => $usageState,
                ),
                'stats' => $stats,
            );
        }
    }


    public function formatSize($value) {
        return $this->useFormatter ? Yii::$app->formatter->asShortSize($value, 1) : self::size2Str($value, 1);
    }

    public function formatPercent($value) {
        return $this->useFormatter ? Yii::$app->formatter->asPercent($value, 2) : (round(100 * $value, 2, PHP_ROUND_HALF_UP).'%');
    }

    public function formatDateTime($value) {
        return $this->useFormatter ? Yii::$app->formatter->asDatetime($value) : date('Y-m-d H:i:s', $value);
    }

    public function formatExecTime($value) {
        return $this->useFormatter ? Yii::$app->formatter->asRelativeTime($value, 0) : self::execTime2Str($value);
    }


    protected static function execTime2Str($value) {
        return ($value >= 86400 ? floor($value/86400).' days ': '').gmdate('H:i:s', $value%86400);
    }

    protected static function size2Str($bytes, $decimals = 1) {
        $units = array('B', 'Kb', 'Mb', 'Gb', 'Tb');
        $last = end($units);
        foreach($units as $unit) {
            if ($bytes < 1024 || $unit == $last)
                return round($bytes, $decimals, PHP_ROUND_HALF_UP).' '.$unit;
            $bytes /= 1024;
        }
    }

}