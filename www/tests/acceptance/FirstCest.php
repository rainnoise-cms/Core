<?php 

class FirstCest
{
    public function _before(AcceptanceTester $I)
    {
    }

	// tests
	public function MainPage(AcceptanceTester $I)
	{
		$I->amOnPage('/');
		$I->canSeeResponseCodeIsSuccessful();
		$I->see('It works!');
	}

    // tests
    public function NotExistModule(AcceptanceTester $I)
    {
		$I->amOnPage('/NotExistModule');
		$I->canSeeResponseCodeIsServerError();
		$I->see('Module \'NotExistModule\' does not exist');
    }
}
