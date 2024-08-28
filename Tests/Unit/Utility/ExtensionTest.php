<?php

use PHPUnit\Framework\TestCase;
use bovigo\vfs\vfsStream;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use DrBlitz\GoogleIndexer\Utility\Extension;
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
}