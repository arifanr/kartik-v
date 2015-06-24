<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014
 * @package yii2-widgets
 * @subpackage yii2-widget-depdrop
 * @version 1.0.0
 */

namespace kartik\depdrop;

/**
 * Asset bundle for Dependent Dropdown widget
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class DepDropAsset extends \kartik\base\AssetBundle
{

    public function init()
    {
        $this->setSourcePath('@vendor/kartik-v/dependent-dropdown');
        $this->setupAssets('css', ['css/dependent-dropdown']);
        $this->setupAssets('js', ['js/dependent-dropdown']);
        parent::init();
    }
}
