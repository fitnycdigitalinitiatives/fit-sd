<?php
namespace OmekaTheme\Helper;

use Laminas\View\Helper\AbstractHelper;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;

class ItemDetailsHelper extends AbstractHelper
{
    public function __invoke(AbstractResourceEntityRepresentation $resource)
    {
        $filterLocale = (bool) $this->getView()->siteSetting('filter_locale_values');
        $escape = $this->getView()->plugin('escapeHtml');
        $date = $resource->value('dcterms:date');
        $departments = [];
        if ($resource->resourceName() == "items") {
            foreach ($resource->itemSets() as $itemSet) {
                foreach ($itemSet->value('dcterms:type', ['all' => true]) as $itemSetType) {
                    if (($itemSetType == "Department") || ($itemSetType == "Program")) {
                        $departments[] = $itemSet->displayTitle();
                        break;
                    }
                }
            }
        }

        $html = '<div class="date-class-type">';
        if ($departments) {
            $html .= '<ul class="list-inline mb-0">';
            $html .= '<li class="list-inline-item">' . implode(", ", $departments) . '</li>';
            $html .= '</ul>';
        }
        if ($date) {
            $html .= '<ul class="list-inline mb-0"><li class="date list-inline-item">' . $date->asHtml() . '</li></ul>';
        }

        $html .= '</div>';
        return $html;
    }
}