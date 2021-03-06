<?php

/*!
 * ammana.es - job protocols generator
 * https://github.com/NoLegalTech/ammana
 * Copyright (C) 2018 Zeres Abogados y Consultores Laborales SLP <zeres@zeres.es>
 * https://github.com/NoLegalTech/ammana/blob/master/LICENSE
 */

namespace ProfileBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $this->markTestSkipped('must be revisited.');

        $client = static::createClient();

        $crawler = $client->request('GET', '/profile');

        //$content = $client->getResponse()->getContent();

        $this->assertContains('Tu perfil', $client->getResponse()->getContent());
        $this->assertContains('Nombre de la empresa:', $client->getResponse()->getContent());
        $this->assertContains('CIF:', $client->getResponse()->getContent());
        $this->assertContains('Domicilio social:', $client->getResponse()->getContent());
        $this->assertContains('Persona de contacto:', $client->getResponse()->getContent());
        $this->assertContains('Sector:', $client->getResponse()->getContent());
    }
}
