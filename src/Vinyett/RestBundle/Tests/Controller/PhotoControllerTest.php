<?php

namespace Vinyett\RestBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * PhotoControllerTest class to test PhotoController (which is part of the API)
 * 
 * @extends WebTestCase
 */
class PhotoControllerTest extends WebTestCase
{

    public $client;
    
    public $photo;

    /**
     * setUp function.
     *
     * All tests here require a logged in user, first we must log them in. 
     *
     * @access public
     * @return void
     */
    public function setUp()
    {
        // Perform user login.
        $this->client = static::createClient();
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $this->client->submit($form, array('_username' => 'ghost', '_password' => 'dannyb0y'));
        $this->client->followRedirects();
    }

    /**
     * Tests against the cgetAction.
     * 
     * @access public
     * @return void
     */
    public function testCget()
    {
        $crawler = $this->client->request('GET', '/rest/photos');
                
        /* See if we have an appriopiate response */
        $this->assertTrue(in_array(
            $this->client->getResponse()->getStatusCode(),
            array(200, 404)
        ));
        
        $this->assertTrue(
            $this->client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
        
    }
    
    /**
     * Tests against the getAction.
     * 
     * @access public
     * @return void
     */
    public function testGet()
    { 
        $crawler = $this->client->request('GET', '/rest/photos/16');
        
        $this->assertTrue(in_array(
            $this->client->getResponse()->getStatusCode(),
            array(200, 404)
        ));
        
        $this->assertTrue(
            $this->client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
    }
    
    /**
     * testPut function.
     * 
     * @access public
     * @return void
     */
    public function testPost()
    { 
        $photo = new UploadedFile(
            '/Users/Dan/Sites/Vinyett/web/images/test/test_upload_image.jpg',
            'photo.jpg',
            'image/jpeg'
        );
        
        $this->client->request(
            'POST',
            '/rest/photo',
            array("title" => "test"),
            array('file' => $photo)
        );
        
        $this->assertTrue(in_array(
            $this->client->getResponse()->getStatusCode(),
            array(201, 404)
        ));
        
        $this->assertTrue(
            $this->client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
        
        /* We use this data to test the DELETE, PUT (maybe even GET) */
        return json_decode($this->client->getResponse()->getContent(), true);

    } 
    
    /**
     * testFavorite function.
     * 
     * @access public
     * @return void
     */
    public function testFavorite()
    { 
        /* Testing the faovrite process */
        $crawler = $this->client->request("PATCH", "/rest/photos/16/favorite");
        $this->assertTrue(in_array(
            $this->client->getResponse()->getStatusCode(),
            array(200)
        ));
        
        /* and then the unfavorite process */
        $crawler = $this->client->request("PATCH", "/rest/photos/16/favorite");
        $this->assertTrue(in_array(
            $this->client->getResponse()->getStatusCode(),
            array(200)
        ));
    }
    
    /**
     * testDelete function.
     * 
     * @access public
     * @return void
     * 
     * @depends testPost
     */
    public function testDelete($photo) 
    { 
        $this->client->request('DELETE', '/rest/photos/'.$photo['id']);
        
        $this->assertTrue(in_array(
            $this->client->getResponse()->getStatusCode(),
            array(204)
        ));    
    }
    
    /**
     * testPut function.
     * 
     * @access public
     * @return void
     */
    public function testPut()
    { 
        //$crawler = $this->client->request("PUT", "/rest/photos/16");
    }
    
    
    
    
    
    
}