<?php
namespace kilyakus\widget\dropdown;

class DropDownAsset extends \kilyakus\widgets\AssetBundle
{
    public function init()
    {
        $this->setSourcePath(__DIR__ . '/assets');
        $this->setupAssets('css', ['css/widget-dropdown'],'widget-dropdown');
        parent::init();
    }
}
