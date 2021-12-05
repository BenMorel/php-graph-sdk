<?php
/**
 * Copyright 2017 Facebook, Inc.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
 * Facebook.
 *
 * As with any software that integrates with the Facebook platform, your use
 * of this software is subject to the Facebook Developer Principles and
 * Policies [http://developers.facebook.com/policy/]. This copyright notice
 * shall be included in all copies or substantial portions of the software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 */
namespace Facebook;

use Facebook\GraphNodes\GraphAlbum;
use Facebook\GraphNodes\GraphEdge;
use Facebook\GraphNodes\GraphEvent;
use Facebook\GraphNodes\GraphGroup;
use Facebook\GraphNodes\GraphList;
use Facebook\GraphNodes\GraphNode;
use Facebook\GraphNodes\GraphNodeFactory;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\GraphNodes\GraphObject;
use Facebook\GraphNodes\GraphPage;
use Facebook\GraphNodes\GraphSessionInfo;
use Facebook\GraphNodes\GraphUser;

/**
 * Class FacebookResponse
 *
 * @package Facebook
 */
class FacebookResponse
{
    /**
     * The HTTP status code response from Graph.
     */
    protected ?int $httpStatusCode;

    /**
     * The headers returned from Graph.
     */
    protected array $headers;

    /**
     * The raw body of the response from Graph.
     */
    protected ?string $body;

    /**
     * The decoded body of the Graph response.
     */
    protected array $decodedBody = [];

    /**
     * The original request that returned this response.
     */
    protected FacebookRequest $request;

    /**
     * The exception thrown by this request.
     */
    protected ?FacebookSDKException $thrownException = null;

    /**
     * Creates a new Response entity.
     */
    public function __construct(FacebookRequest $request, ?string $body = null, ?int $httpStatusCode = null, array $headers = [])
    {
        $this->request = $request;
        $this->body = $body;
        $this->httpStatusCode = $httpStatusCode;
        $this->headers = $headers;

        $this->decodeBody();
    }

    /**
     * Return the original request that returned this response.
     */
    public function getRequest(): FacebookRequest
    {
        return $this->request;
    }

    /**
     * Return the FacebookApp entity used for this response.
     */
    public function getApp(): FacebookApp
    {
        return $this->request->getApp();
    }

    /**
     * Return the access token that was used for this response.
     */
    public function getAccessToken(): ?string
    {
        return $this->request->getAccessToken();
    }

    /**
     * Return the HTTP status code for this response.
     */
    public function getHttpStatusCode(): ?int
    {
        return $this->httpStatusCode;
    }

    /**
     * Return the HTTP headers for this response.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Return the raw body response.
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Return the decoded body response.
     */
    public function getDecodedBody(): array
    {
        return $this->decodedBody;
    }

    /**
     * Get the app secret proof that was used for this response.
     */
    public function getAppSecretProof(): ?string
    {
        return $this->request->getAppSecretProof();
    }

    /**
     * Get the ETag associated with the response.
     */
    public function getETag(): ?string
    {
        return isset($this->headers['ETag']) ? $this->headers['ETag'] : null;
    }

    /**
     * Get the version of Graph that returned this response.
     */
    public function getGraphVersion(): ?string
    {
        return isset($this->headers['Facebook-API-Version']) ? $this->headers['Facebook-API-Version'] : null;
    }

    /**
     * Returns true if Graph returned an error message.
     */
    public function isError(): bool
    {
        return isset($this->decodedBody['error']);
    }

    /**
     * Throws the exception.
     *
     * @throws FacebookSDKException
     */
    public function throwException(): void
    {
        throw $this->thrownException;
    }

    /**
     * Instantiates an exception to be thrown later.
     */
    public function makeException(): void
    {
        $this->thrownException = FacebookResponseException::create($this);
    }

    /**
     * Returns the exception that was thrown for this request.
     */
    public function getThrownException(): ?FacebookResponseException
    {
        return $this->thrownException;
    }

    /**
     * Convert the raw response into an array if possible.
     *
     * Graph will return 2 types of responses:
     * - JSON(P)
     *    Most responses from Graph are JSON(P)
     * - application/x-www-form-urlencoded key/value pairs
     *    Happens on the `/oauth/access_token` endpoint when exchanging
     *    a short-lived access token for a long-lived access token
     * - And sometimes nothing :/ but that'd be a bug.
     */
    public function decodeBody(): void
    {
        $decodedBody = json_decode($this->body, true);

        if ($decodedBody === null) {
            $decodedBody = [];
            parse_str($this->body, $decodedBody);
        } elseif (is_bool($decodedBody)) {
            // Backwards compatibility for Graph < 2.1.
            // Mimics 2.1 responses.
            // @TODO Remove this after Graph 2.0 is no longer supported
            $decodedBody = ['success' => $decodedBody];
        } elseif (is_numeric($decodedBody)) {
            $decodedBody = ['id' => $decodedBody];
        }

        if (!is_array($decodedBody)) {
            $decodedBody = [];
        }

        $this->decodedBody = $decodedBody;

        if ($this->isError()) {
            $this->makeException();
        }
    }

    /**
     * Instantiate a new GraphObject from response.
     *
     * @param string|null $subclassName The GraphNode subclass to cast to.
     *
     * @throws FacebookSDKException
     *
     * @deprecated 5.0.0 getGraphObject() has been renamed to getGraphNode()
     * @todo v6: Remove this method
     */
    public function getGraphObject(?string $subclassName = null): GraphObject
    {
        return $this->getGraphNode($subclassName);
    }

    /**
     * Instantiate a new GraphNode from response.
     *
     * @param string|null $subclassName The GraphNode subclass to cast to.
     *
     * @throws FacebookSDKException
     */
    public function getGraphNode(?string $subclassName = null): GraphNode
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphNode($subclassName);
    }

    /**
     * Convenience method for creating a GraphAlbum collection.
     *
     * @throws FacebookSDKException
     */
    public function getGraphAlbum(): GraphAlbum
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphAlbum();
    }

    /**
     * Convenience method for creating a GraphPage collection.
     *
     * @throws FacebookSDKException
     */
    public function getGraphPage(): GraphPage
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphPage();
    }

    /**
     * Convenience method for creating a GraphSessionInfo collection.
     *
     * @throws FacebookSDKException
     */
    public function getGraphSessionInfo(): GraphSessionInfo
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphSessionInfo();
    }

    /**
     * Convenience method for creating a GraphUser collection.
     *
     * @throws FacebookSDKException
     */
    public function getGraphUser(): GraphUser
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphUser();
    }

    /**
     * Convenience method for creating a GraphEvent collection.
     *
     * @throws FacebookSDKException
     */
    public function getGraphEvent(): GraphEvent
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphEvent();
    }

    /**
     * Convenience method for creating a GraphGroup collection.
     *
     * @throws FacebookSDKException
     */
    public function getGraphGroup(): GraphGroup
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphGroup();
    }

    /**
     * Instantiate a new GraphList from response.
     *
     * @param string|null $subclassName The GraphNode subclass to cast list items to.
     * @param bool        $auto_prefix  Toggle to auto-prefix the subclass name.
     *
     * @throws FacebookSDKException
     *
     * @deprecated 5.0.0 getGraphList() has been renamed to getGraphEdge()
     * @todo v6: Remove this method
     */
    public function getGraphList(?string $subclassName = null, bool $auto_prefix = true): GraphList
    {
        return $this->getGraphEdge($subclassName, $auto_prefix);
    }

    /**
     * Instantiate a new GraphEdge from response.
     *
     * @param string|null $subclassName The GraphNode subclass to cast list items to.
     * @param bool        $auto_prefix  Toggle to auto-prefix the subclass name.
     *
     * @throws FacebookSDKException
     */
    public function getGraphEdge(?string $subclassName = null, bool $auto_prefix = true): GraphEdge
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphEdge($subclassName, $auto_prefix);
    }
}
