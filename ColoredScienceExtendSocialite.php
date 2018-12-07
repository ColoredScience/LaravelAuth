<?php

namespace ColoredScience\SocialiteProvider;

use SocialiteProviders\Manager\SocialiteWasCalled;

class ColoredScienceExtendSocialite
{
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite('coloredsci', __NAMESPACE__.'\Provider');
    }
}

