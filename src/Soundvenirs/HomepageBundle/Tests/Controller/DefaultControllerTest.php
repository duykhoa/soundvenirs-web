<?php

namespace Soundvenirs\HomepageBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function setUp()
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $entityManager = $container->get('doctrine.orm.entity_manager');

        $connection = $entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();

        $connection->executeUpdate($platform->getTruncateTableSQL('Sound', true));
    }

    public function testUpload()
    {
        $client = static::createClient();
        $container = $client->getContainer();

        $crawler = $client->request('GET', '/');
        $uploadForm = $crawler->selectButton('Upload')->form();
        $uploadForm['form[soundfile]']->upload(__DIR__ . '/../assets/soundfile.mp3');
        $uploadForm['form[title]'] = 'First Song';
        $client->submit($uploadForm);
        $this->assertTrue($client->getResponse()->isSuccessful());

        $repo = $client->getContainer()->get('soundvenirs_domain.sound_repository');
        $sounds = $repo->findAll();
        $sound = $sounds[0];

        $this->assertEquals('First Song', $sound->title);
        $this->assertTrue(file_exists($container->getParameter('soundfiles_path') . $sound->id . '.mp3'));
        unlink($container->getParameter('soundfiles_path') . $sound->id . '.mp3');
    }

    public function testQrCode()
    {
        $client = static::createClient();
        ob_start();
        $client->request('GET', '/qrcode/123456.png');
        ob_clean();
        $this->assertEquals('image/png', $client->getResponse()->headers->get('content-type'));
    }

    public function testDownload()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $uploadForm = $crawler->selectButton('Upload')->form();
        $uploadForm['form[soundfile]']->upload(__DIR__.'/../assets/soundfile.mp3');
        $client->submit($uploadForm);

        $repo = $client->getContainer()->get('soundvenirs_domain.sound_repository');
        $sounds = $repo->findAll();
        $sound = $sounds[0];

        $client->request('GET', '/download/'.$sound->id.'.mp3');
        $this->assertEquals('foo', $client->getResponse()->getContent());

        $client->request('GET', '/download/a.mp3');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        $client->request('GET', '/download/abcdefg.mp3');
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }
}
