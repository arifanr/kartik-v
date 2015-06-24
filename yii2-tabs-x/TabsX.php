<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014 - 2015
 * @package yii2-tabs-x
 * @version 1.2.0
 */

namespace kartik\tabs;

use Yii;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\helpers\ArrayHelper;
use yii\bootstrap\Dropdown;
use yii\base\InvalidConfigException;

/**
 * An extended Bootstrap Tabs widget for Yii Framework 2 based on the bootstrap-tabs-x
 * plugin by Krajee. This widget enhances the default bootstrap tabs plugin with various
 * new styling enhancements.
 *
 * ```php
 * echo TabsX::widget([
 *     'position' => TabsX::POS_ABOVE,
 *     'align' => TabsX::ALIGN_LEFT,
 *     'items' => [
 *         [
 *             'label' => 'One',
 *             'content' => 'Anim pariatur cliche...',
 *             'active' => true
 *         ],
 *         [
 *             'label' => 'Two',
 *             'content' => 'Anim pariatur cliche...',
 *             'headerOptions' => [],
 *             'options' => ['id' => 'myveryownID'],
 *         ],
 *         [
 *             'label' => 'Dropdown',
 *             'items' => [
 *                  [
 *                      'label' => 'DropdownA',
 *                      'content' => 'DropdownA, Anim pariatur cliche...',
 *                  ],
 *                  [
 *                      'label' => 'DropdownB',
 *                      'content' => 'DropdownB, Anim pariatur cliche...',
 *                  ],
 *             ],
 *         ],
 *     ],
 * ]);
 * ```
 *
 * @see http://plugins.krajee.com/tabs-x
 * @see http://github.com/kartik-v/bootstrap-tabs-x
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class TabsX extends \yii\bootstrap\Tabs
{
    /**
     * Tabs direction / position
     */
    const POS_ABOVE = 'above';
    const POS_BELOW = 'below';
    const POS_LEFT = 'left';
    const POS_RIGHT = 'right';

    /**
     * Tab align
     */
    const ALIGN_LEFT = 'left';
    const ALIGN_CENTER = 'center';
    const ALIGN_RIGHT = 'right';

    /**
     * Tab content fixed heights
     */
    const SIZE_TINY = 'xs';
    const SIZE_SMALL = 'sm';
    const SIZE_MEDIUM = 'md';
    const SIZE_LARGE = 'lg';

    /**
     * @var string the position of the tabs with respect to the tab content Should be
     * one of the [[TabsX::POS]] constants. Defaults to [[TabsX::POS_ABOVE]].
     */
    public $position = self::POS_ABOVE;

    /**
     * @var string the alignment of the tab headers with respect to the tab content. Should be
     * one of the [[TabsX::ALIGN]] constants. Defaults to [[TabsX::ALIGN_LEFT]].
     */
    public $align = self::ALIGN_LEFT;

    /**
     * @var boolean whether the tab content should be boxed within a bordered container.
     * Defaults to `false`.
     */
    public $bordered = false;

    /**
     * @var boolean whether the tab header text orientation should be rotated sideways.
     * Applicable only when position is one of [[TabsX::POS_LEFT]] or [[TabsX::POS_RIGHT]].
     * Defaults to `false`.
     */
    public $sideways = false;

    /**
     * @var boolean whether to fade in each tab pane using the fade animation effect. Defaults
     * to `true`.
     */
    public $fade = true;

    /**
     * @var string whether the tab body content height should be of a fixed size. You should
     * pass one of the [[TabsX::SIZE]] constants. Applicable only when position is one of
     * [[TabsX::POS_ABOVE]] or [[TabsX::POS_BELOW]]. Defaults to empty string (meaning dynamic
     * height).
     */
    public $height = '';

    /**
     * @var array the HTML attributes for the TabsX container
     */
    public $containerOptions = [];

    /**
     * @var array widget JQuery events. You must define events in
     * event-name => event-function format
     * for example:
     * ~~~
     * pluginEvents = [
     *     "change" => "function() { log("change"); }",
     *     "open" => "function() { log("open"); }",
     * ];
     * ~~~
     */
    public $pluginEvents = [];

    /**
     * Initializes the widget.
     */
    public function init()
    {
        parent::init();
        if (empty($this->containerOptions['id'])) {
            $this->containerOptions['id'] = $this->options['id'] . '-container';
        }
        if (ArrayHelper::getValue($this->containerOptions, 'data-enable-cache', true) === false) {
            $this->containerOptions['data-enable-cache'] = "false";
        }
        $this->registerAssets();
        Html::addCssClass($this->options, 'nav ' . $this->navType);
        $this->options['role'] = 'tablist';
        $css = self::getCss("tabs-{$this->position}", $this->position != null) .
            self::getCss("tab-align-{$this->align}", $this->align != null) .
            self::getCss("tab-bordered", $this->bordered) .
            self::getCss("tab-sideways", $this->sideways && ($this->position == self::POS_LEFT || $this->position == self::POS_RIGHT)) .
            self::getCss("tab-height-{$this->height}", $this->height != null && ($this->position == self::POS_ABOVE || $this->position == self::POS_BELOW));
        Html::addCssClass($this->containerOptions, 'tabs-x' . $css);
    }

    /**
     * Renders the widget.
     */
    public function run()
    {
        echo $this->renderItems();
    }

    /**
     * Parse the CSS content to append based on condition
     *
     * @param string $prop the css property
     * @param boolean $condition the validation to append the CSS class
     * @return string the parsed CSS
     */
    protected static function getCss($prop = '', $condition = true)
    {
        return $condition ? ' ' . $prop : '';
    }

    /**
     * Renders tab items as specified on [[items]].
     *
     * @return string the rendering result.
     * @throws InvalidConfigException.
     */
    protected function renderItems()
    {
        $headers = [];
        $panes = [];

        if (!$this->hasActiveTab() && !empty($this->items)) {
            $this->items[0]['active'] = true;
        }

        foreach ($this->items as $n => $item) {
            if (!isset($item['label'])) {
                throw new InvalidConfigException("The 'label' option is required.");
            }
            $encodeLabel = isset($item['encode']) ? $item['encode'] : $this->encodeLabels;
            $label = $encodeLabel ? Html::encode($item['label']) : $item['label'];
            $headerOptions = array_merge($this->headerOptions, ArrayHelper::getValue($item, 'headerOptions', []));
            $linkOptions = array_merge($this->linkOptions, ArrayHelper::getValue($item, 'linkOptions', []));
            $content = ArrayHelper::getValue($item, 'content', '');
            
            if (isset($item['items'])) {
                $label .= ' <b class="caret"></b>';
                Html::addCssClass($headerOptions, 'dropdown');

                if ($this->renderDropdown($n, $item['items'], $panes)) {
                    Html::addCssClass($headerOptions, 'active');
                }

                Html::addCssClass($linkOptions, 'dropdown-toggle');
                $linkOptions['data-toggle'] = 'dropdown';
                $header = Html::a($label, "#", $linkOptions) . "\n"
                    . Dropdown::widget(['items' => $item['items'], 'clientOptions' => false, 'view' => $this->getView()]);
            } else {
                $options = array_merge($this->itemOptions, ArrayHelper::getValue($item, 'options', []));
                $options['id'] = ArrayHelper::getValue($options, 'id', $this->options['id'] . '-tab' . $n);
                $css = 'tab-pane';
                $isActive = ArrayHelper::remove($item, 'active');
                if ($this->fade) {
                    $css = $isActive ? "{$css} fade in" : "{$css} fade";
                }
                Html::addCssClass($options, $css);
                if ($isActive) {
                    Html::addCssClass($options, 'active');
                    Html::addCssClass($headerOptions, 'active');
                }
                $linkOptions['data-toggle'] = 'tab';
                $linkOptions['role'] = 'tab';
                $header = Html::a($label, '#' . $options['id'], $linkOptions);
                $panes[] = Html::tag('div', $content, $options);
            }

            $headers[] = Html::tag('li', $header, $headerOptions);
        }
        $headerContent = Html::tag('ul', implode("\n", $headers), $this->options);
        $paneContent = Html::tag('div', implode("\n", $panes), ['class' => 'tab-content']);
        $tabs = $headerContent . "\n" . $paneContent;
        if ($this->position == self::POS_BELOW) {
            $tabs = $paneContent . "\n" . $headerContent;
        }
        return Html::tag('div', $tabs, $this->containerOptions);
    }

    /**
     * Registers the needed assets
     */
    public function registerAssets()
    {
        $view = $this->getView();
        TabsXAsset::register($view);
        $id = $this->containerOptions['id'];
        if (!empty($this->pluginEvents)) {
            foreach ($this->pluginEvents as $event => $handler) {
                $function = new JsExpression($handler);
                $script .= "{$id}.on('{$event}', {$function});\n";
            }
        }
    }
}