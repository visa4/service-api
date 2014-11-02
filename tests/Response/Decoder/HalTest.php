<?php
/**
 * Matryoshka Service API
 *
 * @link        https://github.com/matryoshka-model/service-api
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaServiceApiTest\Response\Decoder;

use Matryoshka\Service\Api\Response\Decoder\Hal;
use Zend\Http\Response;
use Zend\Json\Json;

/**
 * Class HalTest
 */
class HalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Hal
     */
    protected $decoder;

    public function setUp()
    {
        $this->decoder = new Hal();
    }


    /**
     * @return array
     */
    public function decoderDataProvider()
    {
        //Test simple Json response
        $response1 = new Response();
        $response1->setContent('{"test":"test","test1":"test1"}');
        $response1->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $result1 = ['test' => 'test', 'test1' => 'test1'];

        //Test HAL json
        $response2 = new Response();
        $response2->setContent(
            '{"_links":{"self":{"href":"http://test/user"}},"_embedded":{"users":[{"test":"test","test1":"test1","_links":{"self":{"href":"http://test/user/1"}}},{"test":"foo","test1":"baz","_links":{"self":{"href":"http://test/user/2"}}}]}}'
        );
        $response2->getHeaders()->addHeaderLine('Content-Type', 'application/hal+json');
        $result2 = [
            ['test' => 'test', 'test1' => 'test1'],
            ['test' => 'foo', 'test1' => 'baz'],
        ];

        //Test empty string
        $response3 = new Response();
        $response3->setContent('');
        $response3->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $result3 = [];

        //Test empty Json list
        $response4 = new Response();
        $response4->setContent('[]');
        $response4->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $result4 = [];


        //Test empty HAL json
        $response5 = new Response();
        $response5->setContent(
            '{"_links":{"self":{"href":"http:\/\/example.net\/user"}},"_embedded":{"users":[]},"page_count":0,"page_size":10,"total_items":0}'
        );
        $response5->getHeaders()->addHeaderLine('Content-Type', 'application/hal+json');
        $result5 = [];

        //Test nested resources
        $response6 = new Response();
        $response6->getHeaders()->addHeaderLine('Content-Type', 'application/hal+json');
        $response6->setContent('{
  "_links": {
    "self": {
      "href": "http://example.com/api/status"
    },
    "next": {
      "href": "http://example.com/api/status?page=2"
    },
    "last": {
      "href": "http://example.com/api/status?page=100"
    }
  },
  "count": 2973,
  "per_page": 30,
  "page": 1,
  "_embedded": {
    "status": [
      {
        "_links": {
          "self": {
            "href": "http://example.com/api/status/1347"
          }
        },
        "id": "1347",
        "timestamp": "2013-02-11 23:33:47",
        "status": "This is my awesome status update!",
        "_embedded": {
          "user": {
            "_links": {
              "self": {
                "href": "http://example.com/api/user/mwop"
              }
            },
            "id": "mwop",
            "name": "Matthew Weier O\'Phinney",
            "url": "http://mwop.net"
          }
        }
      }
    ]
  }
}');
        $result6 = array (
          0 => array (
            'id' => '1347',
            'timestamp' => '2013-02-11 23:33:47',
            'status' => 'This is my awesome status update!',
            'user' =>
            array (
              'id' => 'mwop',
              'name' => 'Matthew Weier O\'Phinney',
              'url' => 'http://mwop.net',
            ),
          ),
        );

        //Test single resource
        $response7 = new Response();
        $response7->getHeaders()->addHeaderLine('Content-Type', 'application/hal+json');
        $response7->setContent('{
    "_links": {
        "self": {"href": "http://example.com/api/status/1347"}
    },
    "id": "1347",
    "timestamp": "2013-02-11 23:33:47",
    "status": "This is my awesome status update!",
    "_embedded": {
        "user": {
            "_links": {
                "self": {"href": "http://example.com/api/user/mwop"}
            },
            "id": "mwop",
            "name": "Matthew Weier O\'Phinney",
            "url": "http://mwop.net"
        }
    }
}');
        $result7 = array (
          'id' => '1347',
          'timestamp' => '2013-02-11 23:33:47',
          'status' => 'This is my awesome status update!',
          'user' =>
          array (
            'id' => 'mwop',
            'name' => 'Matthew Weier O\'Phinney',
            'url' => 'http://mwop.net',
          ),
        );


//         $response3 = new Response();
//         $response3->setContent('<resource rel="self" href="/" xmlns:ex="http://example.org/rels/">
//   <link rel="ex:look" href="/bleh" />
//   <link rel="ex:search" href="/search?term={searchTerm}" />
//   <resource rel="ex:member" name="1" href="/foo">
//     <link rel="ex:created_by" href="/some_dude" />
//     <example>bar</example>
//     <resource rel="ex:status" href="/foo;status">
//       <some_property>disabled</some_property>
//     </resource>
//   </resource>
//   <resource rel="ex:member" name="2" href="/bar">
//     <link rel="ex:created_by" href="http://example.com/some_other_guy" />
//     <example>bar</example>
//     <resource rel="ex:status" href="/foo;status">
//       <some_property>disabled</some_property>
//     </resource>
//   </resource>
//   <link rel="ex:widget" name="1" href="/chunky" />
//   <link rel="ex:widget" name="2" href="/bacon" />
// </resource>');
//         $response3->getHeaders()->addHeaderLine('Content-Type', 'application/hal+xml');
//         $result3 = [
//             ['test' => 'test', 'test1' => 'test1'],
//             ['test' => 'foo', 'test1' => 'baz'],
//         ];

        return [
            [$response1, $result1],
            [$response2, $result2, true],
            [$response3, $result3],
            [$response4, $result4],
            [$response5, $result5, true],
            [$response6, $result6, true],
            [$response7, $result7],
        ];
    }


    /**
     * @param Response $response
     * @param array $result
     * @dataProvider decoderDataProvider
     */
    public function testDecode(Response $response, array $result, $isCollection = false)
    {
        $this->decoder->setPromoteTopCollection(true);
        $this->assertEquals($result, $this->decoder->decode($response));
        $this->assertEquals(Json::decode($response->getBody(), Json::TYPE_ARRAY), $this->decoder->getLastPayload());

        if ($isCollection && $response->getHeaders()->get('content-type')->match('*/hal+*')) {
            $this->decoder->setPromoteTopCollection(false);
            $decoded = $this->decoder->decode($response);
            $payload = $this->decoder->getLastPayload();
            $topCollectionName = null;
            foreach ($decoded as $key => $value) {
                if (!$topCollectionName && !isset($payload[$key]) && isset($payload['_embedded'][$key])) {
                    $topCollectionName = $key;
                } else {
                    $this->assertEquals($payload[$key], $decoded[$key]);
                }
            }
            if ($topCollectionName) {
                $this->assertEquals($result, $decoded[$topCollectionName]);
            }
        }
    }

    /**
     * @expectedException \Matryoshka\Service\Api\Exception\RuntimeException
     */
    public function testDecodeShouldThrowExceptionWhenMultipleTopCollections()
    {
        $response = new Response();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/hal+json');
        $response->setContent(
            '{"_embedded":{"collectionOne":[{"foo":"bar"}], "collectionTwo":[{"foo":"baz"}]},"page_count":1,"page_size":10,"total_items":2}'
        );

        $this->decoder->setPromoteTopCollection(true);
        $this->decoder->decode($response);
    }

    /**
     * @expectedException \Matryoshka\Service\Api\Exception\InvalidResponseException
     */
    public function testDecodeShouldThrowExceptionWhenContentTypeMissing()
    {
        $response = new Response();
        $this->decoder->decode($response);
    }

    /**
     * @expectedException \Matryoshka\Service\Api\Exception\InvalidFormatException
     */
    public function testDecodeShouldThrowExceptionWhenInvalidResponseFormat()
    {
        $response = new Response();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/invalid');
        $this->decoder->decode($response);
    }

    public function testGetLastPayLoad()
    {
        $this->assertNull($this->decoder->getLastPayload());
    }

    public function testGetAcceptHeader()
    {
        $accept = $this->decoder->getAcceptHeader();
        $this->assertInstanceOf('\Zend\Http\Header\Accept', $accept);
        $this->assertTrue((bool)$accept->match('application/json'));
    }

    public function testGetSetPromoteTopCollection()
    {
        $this->assertTrue($this->decoder->getPromoteTopCollection()); //default is true
        $this->assertSame($this->decoder, $this->decoder->setPromoteTopCollection(false));
        $this->assertFalse($this->decoder->getPromoteTopCollection());
    }
}
