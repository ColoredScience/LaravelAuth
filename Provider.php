<?php
namespace ColoredScience\LaravelAuth;
use Illuminate\Support\Arr;
use Laravel\Socialite\Two\ProviderInterface;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider implements ProviderInterface
{
    /**
     * Unique Provider Identifier.
     */
    const IDENTIFIER = 'COLOREDSCIENCE';
    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = [];
    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://user.coloredscience.com/oauth/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://user.coloredscience.com/oauth/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $userUrl = 'http://user.coloredscience.com/api/user';

        $response = $this->getHttpClient()->get($userUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        return json_decode($response->getBody(), true);

        // $userUrl = 'https://api.bitbucket.org/2.0/user?access_token='.$token;

        // $response = $this->getHttpClient()->get($userUrl);

        // $user = json_decode($response->getBody(), true);

        // if (in_array('email', $this->scopes)) {
        //     $user['email'] = $this->getEmailByToken($token);
        // }

        // return $user;
    }

    /**
     * Get the email for the given access token.
     *
     * @param  string  $token
     * @return string|null
     */
    protected function getEmailByToken($token)
    {
        $emailsUrl = 'https://user.coloredscience.com/emails?access_token='.$token;

        try {
            $response = $this->getHttpClient()->get($emailsUrl);
        } catch (Exception $e) {
            return;
        }

        $emails = json_decode($response->getBody(), true);

        foreach ($emails['values'] as $email) {
            if ($email['type'] == 'email' && $email['is_primary'] && $email['is_confirmed']) {
                return $email['email'];
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id'       => $user['id'],
            'email'    => $user['email'],
            'fname'     => $user['fname'],
            'lname'     => $user['lname'],
        ]);
        // return (new User)->setRaw($user)->map([
        //     'id' => $user['uuid'], 'nickname' => $user['username'],
        //     'name' => Arr::get($user, 'display_name'), 'email' => Arr::get($user, 'email'),
        //     'avatar' => Arr::get($user, 'links.avatar.href'),
        // ]);
    }

    /**
     * Get the access token for the given code.
     *
     * @param  string  $code
     * @return string
     */
    public function getAccessToken($code)
    {
        $postKey = (version_compare(ClientInterface::VERSION, '6') === 1) ? 'form_params' : 'body';

        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'auth' => [$this->clientId, $this->clientSecret],
            'headers' => ['Accept' => 'application/json'],
            $postKey => $this->getTokenFields($code),
        ]);

        return json_decode($response->getBody(), true)['access_token'];
    }

    /**
     * {@inheritdoc}
     */
    // protected function getTokenFields($code)
    // {
    //     return array_merge(parent::getTokenFields($code), [
    //         'grant_type' => 'authorization_code',
    //     ]);
    // }

    protected function getTokenFields($code)
    {
        return [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUrl,
        ];
        // return parent::getTokenFields($code) + ['grant_type' => 'authorization_code'];
    }
}