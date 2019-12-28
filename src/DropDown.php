<?php
namespace kilyakus\widget\dropdown;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\ArrayHelper;
use kilyakus\button\Button;

/*

    For example:
    
    Dropdown::widget([
        'button' => [
            'icon' => 'fa fa-cog',
            'iconPosition' => Widget\Button::ICON_POSITION_LEFT,
            'type' => Widget\Button::TYPE_PRIMARY,
            'size' => Widget\Button::SIZE_MINI,
            'disabled' => false,
            'block' => false,
            'outline' => true,
            'hover' => true,
            'circle' => true,
            'options' => ['title' => Yii::t('easyii', 'Actions')]
        ],
       'title' => 'Dropdown title',
       'more' => ['label' => 'xxx', 'url' => '/', 'icon' => 'm-icon-swapright'],
       'scroller' => ['height' => 200],
       'items' => [
           ['label' => 'Level 1 - Dropdown A', 'url' => '#'],
           '<li class="divider"></li>',
           '<li class="dropdown-header">Dropdown Header</li>',
           ['label' => 'Level 1 - Dropdown B', 'url' => '#'],
        ],
    ]);
    
 */
class DropDown extends \yii\bootstrap\Dropdown
{
    public $pluginName = 'dropdown';

    const THEME_DEFAULT = 'default';
    const THEME_DARK = 'dark';
    const THEME_LIGHT = 'light';

    public $theme = self::THEME_DEFAULT;

    public $encodeLabels = false;

    public $title;

    public $button;

    /**
     * @var array the dropdown last item options
     * with the following structure:
     * ```php
     * [
     *     // optional, item label
     *     'label' => 'Show all messages',
     *     // optional, item icon
     *     'icon' => 'm-icon-swapright',
     *     // optional, item url
     *     'url' => '/',
     * ]
     * ```
     */
    public $more = [];

    /**
     * @var array the dropdown item options
     * is an array of the following structure:
     * ```php
     * [
     *   // required, height of the body portlet as a px
     *   'height' => 150,
     *   // optional, HTML attributes of the scroller
     *   'options' => [],
     *   // optional, footer of the scroller. May contain string or array(the options of Link component)
     *   'footer' => [
     *     'label' => 'Show all',
     *   ],
     * ]
     * ```
     */
    public $scroller = [];

    public $placement = 'dropdown';

    protected $container = [];

    protected static $_inbuiltThemes = [
        self::THEME_DEFAULT,
        self::THEME_DARK,
        self::THEME_LIGHT,
    ];

    public function run()
    {
        echo $this->renderItems($this->items);

        $this->registerAssets();
    }

    protected function renderItems($items, $options = [])
    {
        if(isset($this->button)){

            Html::addCssClass($this->container, $this->placement);

            echo Html::beginTag('div',$this->container);
            $this->button['options'] = ArrayHelper::merge($this->button['options'],['id' => $this->id, 'data-toggle' => 'dropdown']);
            echo Button::widget($this->button);
        }
        $lines = [];
        if ($this->title)
        {
            $lines[] = Html::tag('li', $this->title, ['class' => 'dropdown-header']);
        }

        if (!empty($this->scroller))
        {
            if (!isset($this->scroller['height']))
            {
                throw new InvalidConfigException("The 'height' option of Scroller is required.");
            }
            $lines[] = Html::beginTag('li');
            $lines[] = Html::beginTag(
                            'ul', [
                        'style' => 'height: ' . $this->scroller['height'] . 'px;',
                        'class' => 'dropdown-menu-list scroller'
                            ]
            );
        }

        foreach ($items as $i => $item)
        {
            if (isset($item['visible']) && !$item['visible'])
            {
                unset($items[$i]);
                continue;
            }
            if (is_string($item))
            {
                $lines[] = $item;
                continue;
            }

            if (array_key_exists('divider', $item))
            {
                $lines[] = Html::tag('li', '', ['class' => 'divider']);
                continue;
            }

            if (!isset($item['label']))
            {
                throw new InvalidConfigException("The 'label' option is required.");
            }
            $label = $this->encodeLabels ? Html::encode($item['label']) : $item['label'];

            $icon = ArrayHelper::getValue($item, 'icon', null);
            if ($icon)
            {
                $label = Html::tag('i', '', ['alt' => $label, 'class' => $icon]) . ' ' . $label;
            }
            $label .= ArrayHelper::getValue($item, 'badge', '');
            $options = ArrayHelper::getValue($item, 'options', []);
            $linkOptions = ArrayHelper::getValue($item, 'linkOptions', []);
            $linkOptions['tabindex'] = '-1';
            $content = Html::a($label, ArrayHelper::getValue($item, 'url', 'javascript://'), $linkOptions);
            $lines[] = Html::tag('li', $content, $options);
        }

        if (!empty($this->scroller))
        {
            $lines[] = Html::endTag('ul');
            $lines[] = Html::endTag('li');
        }

        if (!empty($this->more))
        {
            $url = ArrayHelper::getValue($this->more, 'url', 'javascript://');
            $text = ArrayHelper::getValue($this->more, 'label', '');
            $icon = ArrayHelper::getValue($this->more, 'icon', '');
            if ($icon)
            {
                $icon = Html::tag('i', '', ['class' => $icon]);
            }
            $lines[] = Html::tag('li', Html::tag('a', $text . $icon, ['href' => $url]), ['class' => 'external']);
        }
        echo Html::tag('ul', implode("\n", $lines), array_merge($this->options,['aria-labelledby' => $this->id]));

        if(isset($this->button)){
            echo Html::endTag('div');
        }
    }

    public function registerAssetBundle()
    {
        $view = $this->getView();
        DropDownAsset::register($view);
        // if (in_array($this->theme, self::$_inbuiltThemes)) {
        //     $bundleClass = __NAMESPACE__ . '\Theme' . Inflector::id2camel($this->theme) . 'Asset';
        //     $bundleClass::register($view);
        // }
    }

    public function registerAssets()
    {
        $this->registerAssetBundle();
        $this->registerPlugin($this->pluginName);
    }
}