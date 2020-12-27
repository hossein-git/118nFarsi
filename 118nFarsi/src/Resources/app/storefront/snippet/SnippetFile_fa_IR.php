<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace I18nFarsi\Resources\app\storefront\snippet;

use Shopware\Core\System\Snippet\Files\SnippetFileInterface;
use I18nFarsi\I18nFarsi;

class SnippetFile_fa_IR implements SnippetFileInterface
{
    public function getName(): string
    {
        return 'storefront.' . I18nFarsi::SWAG_I18N_LOCALE_CODE;
    }

    public function getPath(): string
    {
        return __DIR__ . '/' . $this->getName() . '.json';
    }

    public function getIso(): string
    {
        return I18nFarsi::SWAG_I18N_LOCALE_CODE;
    }

    public function getAuthor(): string
    {
        return 'Shopware Services';
    }

    public function isBase(): bool
    {
        return false;
    }
}
