<?php

namespace Mesd\Jasper\ReportBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contains actions to help with display and interface.
 * To use these actions the report bundle's routes will need to be included into the main projects route file
 */
class ReportController extends ContainerAware
{
    ///////////////
    // CONSTANTS //
    ///////////////

    const FORMAT_PDF = 'pdf';

    /////////////
    // ACTIONS //
    /////////////

    /**
     * Renders an asset from a cached report
     *
     * @param  string $asset     The image path relative to the cache folder (e.g. images/img_0_0_2.png)
     * @param  string $requestId The request id of the report the asset is attached to
     *
     * @return Response          The raw asset
     */
    public function displayCachedAssetAction(
        $asset,
        $requestId
    ) {
        $asset = $this->container->get('mesd.jasper.report.loader')->getReportLoader()->getCachedAsset($asset, $requestId);
        return new Response($asset, 200, []);
    }

    /**
     * Serves report exports
     *
     * @param  string $requestId The request id of the report to export
     * @param  string $format    The format of the report to return
     *
     * @return Response          The exported report
     */
    public function exportCachedReportAction(
        $requestId,
        $format
    ) {
        //Get the export data
        $export = $this->container->get('mesd.jasper.report.loader')->getReportLoader()->getCachedReport($requestId, $format);

        //Create the response
        $response = new Response();

        //Set the headers
        if (self::FORMAT_PDF === $format) {
            $response->headers->set('Content-Type', 'application/pdf');
        }
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $export->getUri() . '.' . $format . '"');
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        //Set the content of the response
        $response->setContent($export->getOutput());

        //Return the final response
        return $response;
    }

    /**
     * Get the options for an ajax selector
     *
     * @param  Request      $request The request
     * @param  string       $inputId The input control id
     *
     * @return JsonResponse          The requested list of options in json format
     */
    public function ajaxOptionsAction(
        Request $request,
                $inputId
    ) {
        // Get the stuff from the request
        $limit  = $request->query->has('limit') ? $request->query->get('limit') : 20;
        $page   = $request->query->has('page') ? $request->query->get('page') : 1;
        $search = $request->query->has('search') ? urldecode($request->query->get('search')) : null;

        // Get the list of choices
        if ($this->container->get('mesd.jasper.report.client')->getOptionsHandler()->supportsAjaxOption($inputId)) {
            $options = $this->container->get('mesd.jasper.report.client')->getOptionsHandler()->getAjaxList($inputId, $limit, $page, $search);
            $choices = [$inputId => []];
            foreach ($options as $option) {
                $choices[$inputId][] = ['value' => $option->getId(), 'text' => $option->getLabel()];
            }
        } else {
            throw new \Exception(sprintf('The control id "%s" does not support ajax options.', $inputId));
        }

        // Return the json list
        return new JsonResponse($choices);
    }
}
