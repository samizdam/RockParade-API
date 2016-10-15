<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Fixture\BandFixture;
use AppBundle\Fixture\UserFixture;
use Tests\FunctionalTester;

/**
 * @author Vehsamrak
 */
class BandControllerTest extends FunctionalTester
{

    const BAND_NAME_FIRST = 'Banders';
    const BAND_NAME_FIRST_EDITED = 'New Derbans';
    const BAND_NAME_SECOND = 'Derbans';
    const BAND_NAME_EXISTING = 'Existing Band';
    const BAND_DESCRIPTION_FIRST = 'Band description.';
    const BAND_DESCRIPTION_FIRST_EDITED = 'New Derbans description.';
    const BAND_DESCRIPTION_SECOND = 'Derband description.';
    const BAND_USER_LOGIN_FIRST = 'bander';
    const BAND_USER_LOGIN_SECOND = 'derban';
    const BAND_USER_LOGIN_THIRD = 'rocker';
    const USER_DESCRIPTION_SHORT_FIRST = 'first description';
    const USER_DESCRIPTION_SHORT_SECOND = 'hard rocker guitarist';
    const USER_DESCRIPTION_FIRST = 'Long description of first user';
    const USER_DESCRIPTION_SECOND = 'Hard rocker was the second musician in this band.';
    const BAND_MEMBER_FIRST_SHORT_DESCRIPTION = 'bass guitar';
    const BAND_MEMBER_FIRST_DESCRIPTION = 'loremus unitus';
    const BAND_MEMBER_SECOND_DESCRIPTION = 'secondus shortus';
    const BAND_MEMBER_SECOND_SHORT_DESCRIPTION = 'violin';
    const USER_LOGIN_EXECUTOR = 'first';

    /** {@inheritDoc} */
    protected function setUp()
    {
        $this->loadFixtures(
            [
                UserFixture::class,
                BandFixture::class,
            ]
        );
        parent::setUp();
    }

    /** @test */
    public function listAction_GETBandsRequest_listAllBands()
    {
        $this->followRedirects();

        $this->sendGetRequest('/api/bands');
        $listBandsResponseCode = $this->getResponseCode();
        $contents = $this->getResponseContents();

        $this->assertEquals(200, $listBandsResponseCode);
        $this->assertEquals(self::BAND_NAME_FIRST, $contents['data'][0]['name']);
        $this->assertEquals(self::BAND_DESCRIPTION_FIRST, $contents['data'][0]['description']);
        $this->assertTrue(array_key_exists('total', $contents));
        $this->assertTrue(array_key_exists('limit', $contents));
        $this->assertTrue(array_key_exists('offset', $contents));
    }

    /** @test */
    public function createAction_POSTBandCreateEmptyRequest_validationErrors()
    {
        $this->sendPostRequest('/api/band', []);
        $responseCode = $this->getResponseCode();
        $errors = $this->getResponseContents()['errors'];

        $this->assertEquals(400, $responseCode);
        $this->assertContains('Parameter is mandatory: name.', $errors);
        $this->assertContains('Parameter is mandatory: description.', $errors);
    }

    /** @test */
    public function createAction_POSTBandCreateRequest_bandCreated()
    {
        $parameters = [
            'name'        => self::BAND_NAME_SECOND,
            'description' => self::BAND_DESCRIPTION_SECOND,
            'members'     => [
                [
                    'login'             => self::BAND_USER_LOGIN_FIRST,
                    'short_description' => self::USER_DESCRIPTION_SHORT_FIRST,
                ],
                [
                    'login'             => self::BAND_USER_LOGIN_SECOND,
                    'short_description' => self::USER_DESCRIPTION_SHORT_SECOND,
                ],
            ],
        ];

        $this->sendPostRequest('/api/band', $parameters);
        $responseCode = $this->getResponseCode();
        $responseLocation = $this->getResponseLocation();

        $this->assertEquals(201, $responseCode);
        $this->assertRegExp('/^http(.*)\/band\/.{8}$/', $responseLocation);

        $this->sendGetRequest('/api/bands');
        $responseCode = $this->getResponseCode();
        $contentsData = $this->getResponseContents()['data'];

        $this->assertEquals(200, $responseCode);
        $this->assertEquals(self::BAND_NAME_SECOND, $contentsData[2]['name']);
        $this->assertEquals(self::BAND_DESCRIPTION_SECOND, $contentsData[2]['description']);
        $this->assertEquals(self::USER_LOGIN_EXECUTOR, $contentsData[2]['creator']);
        $this->assertContains(self::USER_LOGIN_EXECUTOR, $contentsData[2]['members'][0]['login']);
        $this->assertContains(self::BAND_USER_LOGIN_FIRST, $contentsData[2]['members'][1]['login']);
        $this->assertContains(self::USER_DESCRIPTION_SHORT_FIRST, $contentsData[2]['members'][1]['short_description']);
        $this->assertContains(self::BAND_USER_LOGIN_SECOND, $contentsData[2]['members'][2]['login']);
        $this->assertContains(self::USER_DESCRIPTION_SHORT_SECOND, $contentsData[2]['members'][2]['short_description']);
    }

    /** @test */
    public function viewAction_GETBandNameRequest_singleBandInfo()
    {
        $this->sendGetRequest('/api/band/Banders');
        $contents = $this->getResponseContents();
        $responseCode = $this->getResponseCode();

        $this->assertEquals(200, $responseCode);
        $this->assertEquals(self::BAND_NAME_FIRST, $contents['data']['name']);
        $this->assertEquals(self::BAND_DESCRIPTION_FIRST, $contents['data']['description']);
    }

    /** @test */
    public function viewAction_GETBandNotExistingNameRequest_bandNotFoundError()
    {
        $this->sendGetRequest('/api/band/VeryUnexistingBand');
        $contents = $this->getResponseContents();
        $responseCode = $this->getResponseCode();

        $this->assertEquals(404, $responseCode);
        $this->assertContains('Band "VeryUnexistingBand" was not found.', $contents['errors']);
    }

    /** @test */
    public function editAction_PUTBandNameRequestWithExistingName_validationError()
    {
        $parameters = [
            'name'        => self::BAND_NAME_EXISTING,
            'description' => self::BAND_DESCRIPTION_FIRST,
            'members'     => [
                [
                    'login' => self::BAND_USER_LOGIN_FIRST,
                    'short_description' => self::BAND_MEMBER_FIRST_SHORT_DESCRIPTION,
                ],
            ],
        ];

        $this->sendPutRequest('/api/band/Banders', $parameters);
        $contents = $this->getResponseContents();

        $this->assertEquals(400, $this->getResponseCode());
        $this->assertContains('Band with name "Existing Band" already exists.', $contents['errors']);
    }

    /** @test */
    public function editAction_PUTBandNameRequestWithSameNameAndDifferentDescription_bandUpdatedWithNewParameters()
    {
        $parameters = [
            'name'        => self::BAND_NAME_FIRST,
            'description' => self::BAND_DESCRIPTION_FIRST_EDITED,
        ];

        $this->sendPutRequest('/api/band/Banders', $parameters);
        $this->assertEquals(204, $this->getResponseCode());

        $this->sendGetRequest('/api/band/Banders');
        $contents = $this->getResponseContents();
        $this->assertEquals(200, $this->getResponseCode());
        $this->assertEquals(self::BAND_NAME_FIRST, $contents['data']['name']);
        $this->assertEquals(self::BAND_DESCRIPTION_FIRST_EDITED, $contents['data']['description']);
    }

    /** @test */
    public function editAction_PUTBandNameRequestAndExecutorIsNotCreator_accessDenied()
    {
        $this->givenExecutorNotEventCreator();
        $parameters = [
            'name'        => self::BAND_NAME_FIRST,
            'description' => self::BAND_DESCRIPTION_FIRST_EDITED,
        ];

        $this->sendPutRequest('/api/band/Banders', $parameters);

        $this->assertEquals(403, $this->getResponseCode());
    }

    /** @test */
    public function editAction_PUTBandNameRequestWithNewParameters_bandUpdatedWithNewParameters()
    {
        $this->followRedirects();
        $parameters = [
            'name'        => self::BAND_NAME_FIRST_EDITED,
            'description' => self::BAND_DESCRIPTION_FIRST_EDITED,
            'members'     => [
                [
                    'login'             => self::BAND_USER_LOGIN_THIRD,
                    'short_description' => self::USER_DESCRIPTION_SHORT_SECOND,
                ],
            ],
        ];

        $this->sendPutRequest('/api/band/Banders', $parameters);
        $this->assertEquals(204, $this->getResponseCode());

        $this->sendGetRequest('/api/band/Banders');
        $contents = $this->getResponseContents();
        $this->assertEquals(200, $this->getResponseCode());
        $this->assertEquals(self::BAND_NAME_FIRST_EDITED, $contents['data']['name']);
        $this->assertEquals(self::BAND_DESCRIPTION_FIRST_EDITED, $contents['data']['description']);
    }

    /** @test */
    public function createMemberAction_POSTBandNameMembersRequestWithNewMember_bandMemberAdded()
    {
        $parameters = [
            'ambassador'        => self::BAND_NAME_FIRST,
            'login'             => self::BAND_USER_LOGIN_SECOND,
            'short_description' => self::USER_DESCRIPTION_SHORT_SECOND,
            'description'       => self::USER_DESCRIPTION_SECOND,
        ];

        $this->sendPostRequest('/api/band/members', $parameters);
        $this->assertEquals(201, $this->getResponseCode());

        $this->sendGetRequest('/api/band/Banders');
        $contents = $this->getResponseContents();
        $this->assertEquals('derban', $contents['data']['members'][1]['login']);
        $this->assertEquals('hard rocker guitarist', $contents['data']['members'][1]['short_description']);
    }
    
    /** @test */
    public function deleteMemberAction_DELETEBandNameMemberLoginRequest_bandMemberDeleted()
    {
        $this->sendDeleteRequest('/api/band/Banders/member/first');
        $this->assertEquals(204, $this->getResponseCode());

        $this->sendGetRequest('/api/band/Banders');
        $contents = $this->getResponseContents();
        $this->assertEmpty($contents['data']['members']);
    }
    
    /** @test */
    public function updateMemberAction_PUTBandNameMemberRequest_bandMemberUpdatedWithNewParameters()
    {
        $this->followRedirects();
        $parameters = [
            'ambassador'        => self::BAND_NAME_FIRST,
            'login'             => self::USER_LOGIN_EXECUTOR,
            'short_description' => self::BAND_MEMBER_SECOND_SHORT_DESCRIPTION,
            'description'       => self::BAND_MEMBER_SECOND_DESCRIPTION,
        ];

        $this->sendGetRequest('/api/band/Banders');
        $contents = $this->getResponseContents();
        $this->assertEquals(self::USER_LOGIN_EXECUTOR, $contents['data']['members'][0]['login']);
        $this->assertEquals(self::BAND_MEMBER_FIRST_DESCRIPTION, $contents['data']['members'][0]['description']);
        $this->assertEquals(self::BAND_MEMBER_FIRST_SHORT_DESCRIPTION, $contents['data']['members'][0]['short_description']);

        $this->sendPutRequest('/api/band/Banders/member', $parameters);
        $this->assertEquals(204, $this->getResponseCode());

        $this->sendGetRequest('/api/band/Banders');
        $contents = $this->getResponseContents();
        $this->assertEquals(self::USER_LOGIN_EXECUTOR, $contents['data']['members'][0]['login']);
        $this->assertEquals(self::BAND_MEMBER_SECOND_DESCRIPTION, $contents['data']['members'][0]['description']);
        $this->assertEquals(self::BAND_MEMBER_SECOND_SHORT_DESCRIPTION, $contents['data']['members'][0]['short_description']);
    }

    private function givenExecutorNotEventCreator()
    {
        $this->setAuthToken(UserFixture::TEST_TOKEN_SECOND);
    }
}
