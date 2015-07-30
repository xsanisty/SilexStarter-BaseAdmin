<?php

namespace Xsanisty\Admin\Helper;

use SilexStarter\Contracts\MenuRendererInterface;
use SilexStarter\Menu\MenuContainer;

class NavbarMenuRenderer implements MenuRendererInterface
{
    public function render(MenuContainer $menu)
    {
        return $this->generateHtml($menu);
    }

    protected function generateHtml(MenuContainer $menu)
    {
        $html       = '';
        $firstLevel = '<div class="item dropdown">'.
                        '<a href="%s" class="dropdown-toggle" data-toggle="dropdown">'.
                            '<i class="fa fa-%s fa-fw"></i>%s'.
                        '</a>'.
                        '%s'.
                    '</div>';

        foreach ($menu->getItems() as $item) {
            $html .= sprintf(
                $firstLevel,
                Url::to($item->getAttribute('url')),
                $item->getAttribute('icon'),
                $item->getMetaAttribute('counter') ? '<span class="badge">'.$item->getMetaAttribute('counter').'</span>' : '',
                $this->createDropdownList($item->getChildContainer())
            );
        }

        return '<div class="user pull-right">'.$html.'</div>';
    }

    protected function createDropdownList(MenuContainer $menu)
    {
        $header = '<li class="%s dropdown-header">%s</li>';
        $divider= '<li class="divider"></li>';
        $link   = '<li class="%s link"><a id="%s" href="%s">%s %s</a></li>';
        $list   = '';
        $items  = $menu->getItems();

        if (!$items) {
            return '';
        }

        foreach ($items as $item) {
            switch ($item->getAttribute('class')) {
                case 'divider':
                    $list .= $divider;
                    break;
                case 'link':
                    $list .= sprintf(
                        $link,
                        $item->getAttribute('class'),
                        $item->getAttribute('id'),
                        Url::to($item->getAttribute('url')),
                        $item->getAttribute('icon') ? '<i class="fa fa-'.$item->getAttribute('icon').' fa-fw"></i>' : '',
                        $item->getAttribute('label')
                    );
                    break;
                case 'header':
                    $list .= sprintf($header, $item->getAttribute('class'), $item->getAttribute('label'));
            }
        }

        return '<ul class="dropdown-menu dropdown-menu-right">'.$list.'</ul>';
    }
}
