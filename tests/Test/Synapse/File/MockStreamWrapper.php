<?php
namespace Test\Synapse\File;

// @codingStandardsIgnoreFile

class MockStreamWrapper implements StreamWrapperInterface
{

    /**
     * @var resource
     */
    private $stream;

    /**
     *
     * @return bool
     */
    public function dir_closedir()
    {
        return true;
    }

    /**
     * @param string $path
     * @param int    $options
     *
     * @return bool
     */
    public function dir_opendir($path, $options)
    {
        return true;
    }

    /**
     * @return string
     */
    public function dir_readdir()
    {
        return 'directory';
    }

    /**
     * @return bool
     */
    public function dir_rewinddir()
    {
        return true;
    }

    /**
     * @param string $path
     * @param int    $mode
     * @param int    $options
     *
     * @return bool
     */
    public function mkdir($path, $mode, $options)
    {
        return true;
    }

    /**
     * @param string $path_from
     * @param string $path_to
     *
     * @return bool
     */
    public function rename($path_from, $path_to)
    {
        return true;
    }

    /**
     * @param string $path
     * @param int    $options
     *
     * @return bool
     */
    public function rmdir($path, $options)
    {
        return true;
    }

    /**
     * @param int $cast_as
     *
     * @return resource
     */
    public function stream_cast($cast_as)
    {
        return $this->stream;
    }

    /**
     */
    public function stream_close()
    {
        fclose($this->stream);
    }

    /**
     * @return bool
     */
    public function stream_eof()
    {
        return feof($this->stream);
    }

    /**
     * @return bool
     */
    public function stream_flush()
    {
        return true;
    }

    /**
     * @param mixed $operation
     *
     * @return bool
     */
    public function stream_lock($operation)
    {
        return true;
    }

    /**
     * @param string $path
     * @param string $mode
     * @param int    $options
     * @param string &$opened_path
     *
     * @return bool
     */
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $this->stream = fopen('php://temp', $mode);

        return true;
    }

    /**
     * @param int $count
     *
     * @return string
     */
    public function stream_read($count)
    {
        return fread($this->stream, $count);
    }

    /**
     * @param int $offset
     * @param int $whence = SEEK_SET
     *
     * @return bool
     */
    public function stream_seek($offset, $whence = SEEK_SET)
    {
        fseek($this->stream, $offset, $whence);
    }

    /**
     * @param int $option
     * @param int $arg1
     * @param int $arg2
     *
     * @return bool
     */
    public function stream_set_option($option, $arg1, $arg2)
    {
        return true;
    }

    /**
     * @return array
     */
    public function stream_stat()
    {
        return [];
    }

    /**
     * @return int
     */
    public function stream_tell()
    {
        return 1;
    }

    /**
     * @param string $data
     *
     * @return int
     */
    public function stream_write($data)
    {
        fwrite($this->stream, $data);

        return strlen($data);
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function unlink($path)
    {
        return true;
    }

    /**
     * @param string $path
     * @param int    $flags
     *
     * @return array
     */
    public function url_stat($path, $flags)
    {
        return [];
    }
}
