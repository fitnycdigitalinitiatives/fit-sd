<?php

namespace OmekaTheme\Helper;

use Laminas\View\Helper\AbstractHelper;
use Omeka\Api\Representation\ItemRepresentation;

class SocialShare extends AbstractHelper
{
  public function __invoke(ItemRepresentation $item)
  {
    $escape = $this->getView()->plugin('escapeHtml');
    $title = $item->displayTitle();
    $url = $escape($item->url());
    $body = 'From SPARC Digital:%0D%0A' . $title . '%0D%0A' . $url;
    $subject = $title;
    return '
        <!-- Social Share  -->
        <ul id="social-share" class="list-inline mb-0 mt-2 fs-4">
          <li class="list-inline-item">
            <button id="link-copy" class="border-0 bg-transparent p-0 clip-button fit-green" aria-label="Copy item link" title="Copy item link" data-clipboard-text="' . $url . '" data-bs-toggle="tooltip" data-bs-title="Link Copied" data-bs-trigger="manual">
              <i class="fas fa-link" aria-hidden="true">
              </i>
            </button>
          </li>
          <li class="list-inline-item">
            <a class="fit-green" href="mailto:?body=' . $body . '&subject=' . $subject . '" aria-label="Share this item via email" title="Share this item via email">
              <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="bi bi-envelope-fill" viewBox="0 0 16 16">
                <path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414zM0 4.697v7.104l5.803-3.558zM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586zm3.436-.586L16 11.801V4.697z"/>
              </svg>
            </a>
          </li>
          <li class="list-inline-item">
            <a class="fit-green" href="https://www.facebook.com/share.php?u=' . $url . '" aria-label="Share this item on Facebook" title="Share this item on Facebook">
              <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="bi bi-facebook" viewBox="0 0 16 16">
              <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951"/>
            </svg>
            </a>
          </li>
          <li class="list-inline-item">
            <a class="fit-green" href="https://x.com/intent/post?url=' . $url . '" aria-label="Share this item on X" title="Share this item on X">
              <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="bi bi-twitter-x" viewBox="0 0 16 16">
              <path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/>
            </svg>
            </a>
          </li>
        </ul>
        ';
  }
}
