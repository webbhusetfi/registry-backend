<?php
namespace AppBundle\Entity\Repository\Common\Interfaces;

/**
 * Interface for SCRUD.
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
interface ScrudInterface
{
    /**
     * Process search request.
     *
     * @param array $request The request
     *
     * @return array The response
     */
    public function search(array $request);

    /**
     * Process create request.
     *
     * @param array $request The request
     *
     * @return array The response
     */
    public function create(array $request);

    /**
     * Process read request.
     *
     * @param array $request The request
     *
     * @return array The response
     */
    public function read(array $request);

    /**
     * Process update request.
     *
     * @param array $request The request
     *
     * @return array The response
     */
    public function update(array $request);

    /**
     * Process delete request.
     *
     * @param array $request The request
     *
     * @return array The response
     */
    public function delete(array $request);
}

