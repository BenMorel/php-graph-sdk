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
namespace Facebook\FileUpload;

/**
 * Class FacebookTransferChunk
 *
 * @package Facebook
 */
class FacebookTransferChunk
{
    /**
     * The file to chunk during upload.
     */
    private FacebookFile $file;

    /**
     * The ID of the upload session.
     */
    private int $uploadSessionId;

    /**
     * Start byte position of the next file chunk.
     */
    private int $startOffset;

    /**
     * End byte position of the next file chunk.
     */
    private int $endOffset;

    /**
     * The ID of the video.
     */
    private int $videoId;

    public function __construct(FacebookFile $file, int $uploadSessionId, int $videoId, int $startOffset, int $endOffset)
    {
        $this->file = $file;
        $this->uploadSessionId = $uploadSessionId;
        $this->videoId = $videoId;
        $this->startOffset = $startOffset;
        $this->endOffset = $endOffset;
    }

    /**
     * Return the file entity.
     */
    public function getFile(): FacebookFile
    {
        return $this->file;
    }

    /**
     * Return a FacebookFile entity with partial content.
     */
    public function getPartialFile(): FacebookFile
    {
        $maxLength = $this->endOffset - $this->startOffset;

        return new FacebookFile($this->file->getFilePath(), $maxLength, $this->startOffset);
    }

    /**
     * Return upload session Id
     */
    public function getUploadSessionId(): int
    {
        return $this->uploadSessionId;
    }

    /**
     * Check whether is the last chunk
     */
    public function isLastChunk(): bool
    {
        return $this->startOffset === $this->endOffset;
    }

    public function getStartOffset(): int
    {
        return $this->startOffset;
    }

    public function getEndOffset(): int
    {
        return $this->endOffset;
    }

    /**
     * Get uploaded video Id
     */
    public function getVideoId(): int
    {
        return $this->videoId;
    }
}
