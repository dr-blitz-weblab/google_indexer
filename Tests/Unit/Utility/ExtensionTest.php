<?php

use DrBlitz\GoogleIndexer\Utility\Extension;
use bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExtensionTest extends TestCase
{
    public function testIsConfigFileExistsReturnsTrue()
    {
        $root = vfsStream::setup();
        $firstFile = vfsStream::newFile('first_config.json')->at($root)->setContent('');
        $secondFile = vfsStream::newFile('second_config.json')->at($root)->setContent('');
        $firstPath = $firstFile->url();
        $secondPath = $secondFile->url();

        $fisrtSiteMock = $this->createMock(Site::class);
        $fisrtSiteMock->method('getConfiguration')->willReturn(['google_api_key_path' => $firstPath]);
        $secondSiteMock = $this->createMock(Site::class);
        $secondSiteMock->method('getConfiguration')->willReturn(['google_api_key_path' => $secondPath]);

        $siteFinderMock = $this->createMock(SiteFinder::class);
        $siteFinderMock->method('getAllSites')->willReturn([$fisrtSiteMock, $secondSiteMock]);

        GeneralUtility::addInstance(SiteFinder::class, $siteFinderMock);

        $this->assertTrue(Extension::isConfigFileExist());
    }

    public function testIsConfigFileExistsReturnsFalse()
    {
        $root = vfsStream::setup();
        $firstFile = vfsStream::newFile('first_config.json')->at($root)->setContent('');
        $firstPath = $firstFile->url();

        $fisrtSiteMock = $this->createMock(Site::class);
        $fisrtSiteMock->method('getConfiguration')->willReturn(['google_api_key_path' => $firstPath]);
        $secondSiteMock = $this->createMock(Site::class);
        $secondSiteMock->method('getConfiguration')->willReturn(['google_api_key_path' => '']);

        $siteFinderMock = $this->createMock(SiteFinder::class);
        $siteFinderMock->method('getAllSites')->willReturn([$fisrtSiteMock, $secondSiteMock]);

        GeneralUtility::addInstance(SiteFinder::class, $siteFinderMock);

        $this->assertFalse(Extension::isConfigFileExist());
    }

    public function testGetAllDokTypeWithValidConfig()
    {
        $extensionConfigurationMock = $this->createMock(ExtensionConfiguration::class);
        $extensionConfigurationMock->method('get')
            ->with('google_indexer', 'doktype')
            ->willReturn('1,2,3')
        ;

        GeneralUtility::addInstance(ExtensionConfiguration::class, $extensionConfigurationMock);

        $result = Extension::getAllDokType();

        $this->assertEquals(['1', '2', '3'], $result);
    }

    public function testGetAllDokTypeWithEmptyConfig()
    {
        $extensionConfigurationMock = $this->createMock(ExtensionConfiguration::class);
        $exception = new ExtensionConfigurationExtensionNotConfiguredException();
        $extensionConfigurationMock->method('get')->willThrowException($exception);

        GeneralUtility::addInstance(ExtensionConfiguration::class, $extensionConfigurationMock);

        $result = Extension::getAllDokType();

        $this->assertEquals([1], $result);
    }
}