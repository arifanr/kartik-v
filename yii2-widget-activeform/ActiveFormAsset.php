<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015
 * @package yii2-widgets
 * @subpackage yii2-widget-activeform
 * @version 1.4.1
 */

namespace kartik\form;

/**
 * Asset bundle for ActiveForm Widget
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class ActiveFormAsset extends \kartik\base\AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->setSourcePath(__DIR__ . '/assets');
        $this->setupAssets('css', ['css/activeform']);
        parent::init();
    }
}
