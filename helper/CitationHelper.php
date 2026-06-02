<?php

namespace OmekaTheme\Helper;

use Laminas\View\Helper\AbstractHelper;
use Omeka\Api\Representation\ItemRepresentation;

class CitationHelper extends AbstractHelper
{
  public function __invoke(ItemRepresentation $item)
  {
    $escape = $this->getView()->plugin('escapeHtml');
    $chicagoAuthors = '';
    $title = $item->displayTitle();
    $titleCased = $this->titleCase($title);
    $date = $item->value('dcterms:date');
    $url = $escape($item->url());
    $type = $item->displayResourceClassLabel() ? $item->displayResourceClassLabel() : $item->value('dcterms:type');
    $chicagoAuthors = "";
    if ($contributors = $item->value('dcterms:contributor', ['all' => true])) {
      $authorList = [];
      foreach ($contributors as $key => $contributor) {
        if (str_contains($contributor->asHtml(), 'Author') || str_contains($contributor->asHtml(), 'Creator') || str_contains($contributor->asHtml(), 'Designer') || str_contains($contributor->asHtml(), 'Photographer') || str_contains($contributor->asHtml(), 'Artist') || str_contains($contributor->asHtml(), 'Interviewee')) {
          if (!in_array($contributor, $authorList)) {
            $authorList[] = $contributor;
          }
        }
      }
      if (empty($authorList) && (strtolower($type) != 'moving image')) {
        $authorList[] = $contributors[0];
      }
      $len = count($authorList);
      if ($authorList) {
        foreach ($authorList as $authorKey => $author) {
          if (str_contains($author, ",")) {
            $uninvertedName = $firstAndMiddle = trim(explode(",", $author)[1]) . ' ' . trim(explode(",", $author)[0]);
          } else {
            $uninvertedName = $author;
          }
          if ($authorKey == 0) {
            $chicagoAuthors .= $author;
          } elseif (($len - $authorKey) == 1) {
            $chicagoAuthors .= ', and ' . $uninvertedName;
          } else {
            $chicagoAuthors .= ', ' . $uninvertedName;
          }
        }
        $chicagoAuthors .= ". ";
      }
    }
    $chicago = $chicagoAuthors . '"' . $titleCased . '." SPARC Digital, ' . $date . '. <a href="' . $url . '" aria-hidden="true" tabindex="-1">' . $url . '</a>';
    return '
        <div class="input-group my-3">
          <div class="form-control font-monospace text-break" id="chicagoCitation">
          ' . str_replace('..', '.', $chicago) . '
          </div>
          <button class="btn btn-secondary btn-dark rounded-0 clip-button" type="button" id="chicago-button" data-clipboard-target="#chicagoCitation" aria-label="Copy citation to clipboard" title="Copy citation to clipboard">
          <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-clipboard" viewBox="0 0 16 16" aria-hidden="true">
            <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1z"/>
            <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0z"/>
          </svg>
          <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-clipboard-check" viewBox="0 0 16 16" aria-hidden="true" style="display:none">
            <path fill-rule="evenodd" d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
            <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1z"/>
            <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0z"/>
          </svg>
          </button>
        </div>
        ';
  }
  // Converts $title to Title Case, and returns the result.
  protected function titleCase($title)
  {
    $smallwordsarray = array(
      'of',
      'a',
      'the',
      'and',
      'an',
      'or',
      'nor',
      'but',
      'is',
      'if',
      'then',
      'else',
      'when',
      'at',
      'from',
      'by',
      'on',
      'off',
      'for',
      'in',
      'out',
      'over',
      'to',
      'into',
      'with'
    );

    // Split the string into separate words
    $words = explode(' ', $title);

    foreach ($words as $key => $word) {
      // If this word is the first, or it's not one of our small words, capitalise it
      // with ucwords().
      if ($key == 0 or !in_array($word, $smallwordsarray)) {
        $words[$key] = ucwords($word);
      }
    }

    // Join the words back into a string
    $newtitle = implode(' ', $words);

    return $newtitle;
  }
}
