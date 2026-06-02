<?php

namespace OmekaTheme\Helper;

use Laminas\View\Helper\AbstractHelper;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;

class CollectionIconHelper extends AbstractHelper
{
    public function __invoke(AbstractResourceEntityRepresentation $resource)
    {
        $escape = $this->getView()->plugin('escapeHtml');
        $types = $resource->value('dcterms:type', ['all' => true]);
        $class = $resource->displayResourceClassLabel();
        $typesList = [];
        $typeIcons = [];
        foreach ($types as $type) {
            $typesList[] = strtolower($escape($type));
        }
        $typesList[] = strtolower($class);
        $iconList = ["text", "still image", "image", "moving image", "sound"];
        foreach (array_unique($typesList) as $type) {
            if (in_array($type, $iconList)) {
                switch ($type) {
                    case "text":
                        $typeIcons[] = '<svg xmlns="http://www.w3.org/2000/svg" class="bi bi-file-text" viewBox="0 0 16 16" role="img" aria-label="Text">
                        <path d="M5 4a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1zm-.5 2.5A.5.5 0 0 1 5 6h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5M5 8a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1z"/>
                        <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1"/>
                        </svg>';
                        break;
                    case "still image":
                        $typeIcons[] = '<svg xmlns="http://www.w3.org/2000/svg" class="bi bi-images" viewBox="0 0 16 16" role="img" aria-label="Images">
                        <path d="M4.502 9a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/>
                        <path d="M14.002 13a2 2 0 0 1-2 2h-10a2 2 0 0 1-2-2V5A2 2 0 0 1 2 3a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v8a2 2 0 0 1-1.998 2M14 2H4a1 1 0 0 0-1 1h9.002a2 2 0 0 1 2 2v7A1 1 0 0 0 15 11V3a1 1 0 0 0-1-1M2.002 4a1 1 0 0 0-1 1v8l2.646-2.354a.5.5 0 0 1 .63-.062l2.66 1.773 3.71-3.71a.5.5 0 0 1 .577-.094l1.777 1.947V5a1 1 0 0 0-1-1z"/>
                        </svg>';
                        break;
                    case "image":
                        $typeIcons[] = '<svg xmlns="http://www.w3.org/2000/svg" class="bi bi-images" viewBox="0 0 16 16" role="img" aria-label="Images">
                        <path d="M4.502 9a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/>
                        <path d="M14.002 13a2 2 0 0 1-2 2h-10a2 2 0 0 1-2-2V5A2 2 0 0 1 2 3a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v8a2 2 0 0 1-1.998 2M14 2H4a1 1 0 0 0-1 1h9.002a2 2 0 0 1 2 2v7A1 1 0 0 0 15 11V3a1 1 0 0 0-1-1M2.002 4a1 1 0 0 0-1 1v8l2.646-2.354a.5.5 0 0 1 .63-.062l2.66 1.773 3.71-3.71a.5.5 0 0 1 .577-.094l1.777 1.947V5a1 1 0 0 0-1-1z"/>
                        </svg>';
                        break;
                    case "moving image":
                        $typeIcons[] = '<svg xmlns="http://www.w3.org/2000/svg" class="bi bi-film" viewBox="0 0 16 16" role="img" aria-label="Videos">
                        <path d="M0 1a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1zm4 0v6h8V1zm8 8H4v6h8zM1 1v2h2V1zm2 3H1v2h2zM1 7v2h2V7zm2 3H1v2h2zm-2 3v2h2v-2zM15 1h-2v2h2zm-2 3v2h2V4zm2 3h-2v2h2zm-2 3v2h2v-2zm2 3h-2v2h2z"/>
                        </svg>';
                        break;
                    case "sound":
                        $typeIcons[] = '<svg xmlns="http://www.w3.org/2000/svg" class="bi bi-cassette" viewBox="0 0 16 16" role="img" aria-label="Audio">
                        <path d="M4 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2m9-1a1 1 0 1 1-2 0 1 1 0 0 1 2 0M7 6a1 1 0 0 0 0 2h2a1 1 0 1 0 0-2z"/>
                        <path d="M1.5 2A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2zM1 3.5a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-.691l-1.362-2.724A.5.5 0 0 0 12 10H4a.5.5 0 0 0-.447.276L2.19 13H1.5a.5.5 0 0 1-.5-.5zM11.691 11l1 2H3.309l1-2z"/>
                        </svg>';
                        break;
                }
            }
        }
        if ($typeIcons) {
            $html = '<ul class="list-inline mb-0 d-inline-block">';
            foreach ($typeIcons as $icon) {
                $html .= '<li class="type list-inline-item ms-1">' . $icon . '</li>';
            }
            $html .= '</ul>';
            return $html;
        }
    }
}
