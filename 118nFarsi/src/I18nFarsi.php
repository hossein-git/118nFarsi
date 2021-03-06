<?php
declare(strict_types=1);


namespace I18nFarsi;

use Doctrine\DBAL\Connection;
use I18nFarsi\Resources\app\core\snippet\SnippetFile_fa_IR as SnippetFile;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Uuid\Uuid;

class I18nFarsi extends Plugin
{
    public const SWAG_I18N_LOCALE_CODE = 'fa-IR';
    public const SWAG_I18N_LANGUAGE_NAME = 'Farsi';

    public function install(InstallContext $context): void
    {
        $this->addLanguage($context->getContext());
        $this->addBaseSnippetSet($context->getContext());
        parent::install($context);
    }

    private function addLanguage(Context $shopwareContext): void
    {
        $localeId = $this->getLocaleId($shopwareContext);

        if (!$this->isNewLanguage($localeId, $shopwareContext)) {
            return;
        }

        /** @var EntityRepositoryInterface $languageRepository */
        $languageRepository = $this->container->get('language.repository');

        $languageRepository->create(
            [
                [
                    'id' => Uuid::randomHex(),
                    'name' => self::SWAG_I18N_LANGUAGE_NAME,
                    'localeId' => $localeId,
                    'translationCodeId' => $localeId,
                    'createdAt' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ],
            ],
            $shopwareContext
        );
    }

    private function getLocaleId(Context $shopwareContext): string
    {
        /** @var EntityRepositoryInterface $localeRepository */
        $localeRepository = $this->container->get('locale.repository');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('code', self::SWAG_I18N_LOCALE_CODE));

        $localeResult = $localeRepository->searchIds($criteria, $shopwareContext);

        if ($localeResult->getTotal() === 0) {
            throw new \RuntimeException(
                'Invalid locale. Please make sure you entered an existing locale with the correct format: xx-XX'
            );
        }

        $firstId = $localeResult->firstId();
        if ($firstId === null) {
            throw new \RuntimeException(
                'Invalid locale. Please make sure you entered an existing locale with the correct format: xx-XX'
            );
        }

        return $firstId;
    }

    private function isNewLanguage(string $localeId, Context $shopwareContext): bool
    {
        /** @var EntityRepositoryInterface $languageRepository */
        $languageRepository = $this->container->get('language.repository');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('localeId', $localeId));

        $languageResult = $languageRepository->searchIds($criteria, $shopwareContext);

        return $languageResult->getTotal() === 0;
    }

    private function addBaseSnippetSet(Context $shopwareContext): void
    {
        /** @var EntityRepositoryInterface $snippetSetRepository */
        $snippetSetRepository = $this->container->get('snippet_set.repository');

        $snippetSetRepository->create(
            [
                [
                    'id' => Uuid::randomHex(),
                    'name' => 'BASE ' . self::SWAG_I18N_LOCALE_CODE,
                    'baseFile' => (new SnippetFile())->getName(),
                    'iso' => self::SWAG_I18N_LOCALE_CODE,
                    'createdAt' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ],
            ],
            $shopwareContext
        );
    }

    public function uninstall(UninstallContext $context): void
    {
        $connection = $this->container->get(Connection::class);
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS=0;');
        $this->deleteLanguage($context->getContext());
        $this->deleteBaseSnippetSet($context->getContext());
        parent::uninstall($context);
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function deleteLanguage(Context $shopwareContext): void
    {
        /** @var EntityRepositoryInterface $languageRepository */
        $languageRepository = $this->container->get('language.repository');

        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('name', self::SWAG_I18N_LANGUAGE_NAME)
        );

        $languageIds = $languageRepository->searchIds($criteria, $shopwareContext)->getData();
        if (empty($languageIds)) {
            return;
        }

        $languageIds = array_values($languageIds);
        $languageRepository->delete($languageIds, $shopwareContext);
    }

    private function deleteBaseSnippetSet(Context $shopwareContext): void
    {
        /** @var EntityRepositoryInterface $snippetSetRepository */
        $snippetSetRepository = $this->container->get('snippet_set.repository');

        $criteria = new Criteria();
        $criteria->addFilter(
            new MultiFilter(
                'AND', [
                new EqualsFilter('name', 'BASE ' . self::SWAG_I18N_LOCALE_CODE),
                new EqualsFilter('baseFile', (new SnippetFile())->getName()),
            ]
            )
        );

        $setIds = $snippetSetRepository->searchIds($criteria, $shopwareContext)->getData();
        if (empty($setIds)) {
            return;
        }

        $setIds = array_values($setIds);
        $snippetSetRepository->delete($setIds, $shopwareContext);
    }

    public function rebuildContainer(): bool
    {
        return false;
    }
}
