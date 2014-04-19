<?php

namespace MESD\Jasper\ReportBundle\Helper;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

use MESD\Jasper\ReportBundle\Helper\PageLinkManager;

class DisplayHelper {

    ///////////////
    // VARIABLES //
    ///////////////


    /**
     * The Symfony service container
     * @var Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;


    //////////////////
    // BASE METHODS //
    //////////////////


    /**
     * Constructor
     *   NOTE: This helper class requires the twig engine so it can render twigs to pass back to the twig extension
     *         BUT giving this class the templating engine service directly creates a circular reference, which can
     *         be worked around by just using the container
     *
     * @param ContainerInterface $container [description]
     */
    public function __construct(ContainerInterface $container) {
        //Set stuff
        $this->container = $container; 
    }


    ///////////////////
    // CLASS METHODS //
    ///////////////////


    /**
     * Renders a set of page links for html report views
     *
     * @param  JasperClient\Client\Report $report Report Object
     * @param  string                     $route  The route to the action that handles html page loads
     *
     * @return string                             The rendered output of the report page links
     */
    public function renderPageLinks(\JasperClient\Client\Report $report, $route) {
        //Check that the report is in html and has a page and total page number
        if ('html' !== $report->getFormat() || null === $report->getPage() || null === $report->getTotalPages() || null == $route) {
            return '';
        }

        //Generate the urls
        $url = $this->container->get('templating.helper.router')->generate($route, array('page' => '!page!', 'requestId' => $report->getRequestId()));
        $pageLinks = new PageLinkManager();
        $pageLinks->generatePageLinks($url, $report->getPage(), $report->getTotalPages());

        //Render
        return $pageLinks->printLinks();
    }


    //RenderReportView
    //Renders a report and its controls from a report builder object
    public function renderReportView($reportBuilder) {
        //Get the base route back to what action generated the twig that contains the twig function that made this call
        $route = $this->container->get('request')->get('_route');
        $routeHelper = $this->container->get('templating.helper.router');
        $currentPage = $reportBuilder->getReportCurrentPage();
        $lastPage = $reportBuilder->getReportLastPage();

        //var_dump($reportBuilder->debug); die;

        //Generate the links for the control buttons
        $controlLinks = array();
        if ($currentPage > 1) {
            $controlLinks['firstPage'] = $routeHelper->generate($route, array('reportPage' => 1));
        } else {
            $controlLinks['firstPage'] = '#';
        }
        if (($currentPage - 10) > 0) {
            $controlLinks['backTen'] = $routeHelper->generate($route, array('reportPage' => $currentPage - 10));
        } else {
            $controlLinks['backTen'] = '#';
        }
        if (($currentPage - 1) > 0) {
            $controlLinks['back'] = $routeHelper->generate($route, array('reportPage' => $currentPage - 1));
        } else {
            $controlLinks['back'] = '#';
        }
        if (($currentPage) < $lastPage) {
            $controlLinks['next'] = $routeHelper->generate($route, array('reportPage' => $currentPage + 1));
        } else {
            $controlLinks['next'] = '#';
        }
        if (($currentPage + 9) < $lastPage) {
            $controlLinks['nextTen'] = $routeHelper->generate($route, array('reportPage' => $currentPage + 10));
        } else {
            $controlLinks['nextTen'] = '#';
        }
        if ($currentPage < $lastPage) {
            $controlLinks['lastPage'] = $routeHelper->generate($route, array('reportPage' => $lastPage));
        } else {
            $controlLinks['lastPage'] = '#';
        }

        //Render the report viewer template
        return $this->container->get('templating')->render(
            'MESDJasperReportBundle:Report:reportView.html.twig', array(
                'reportOutput'  => $reportBuilder,
                'controlLinks'  => $controlLinks,
            )
        );
    }

    //RenderFolder
    //Takes in a folder collection array from the client's getFolder call and renders the output
    //This will primarily be called from the twig report twig extension
    //generateTree is a flag to determine wether to generate the tree under root or just root's contents
    //maxDepth is the maximum number of levels to go under if generating a tree, 0 is no limit
    //stopAdDefault will prevent a parent link from beign generated when at the default folder
    public function renderFolderView($folderView, $stopAtDefault = true, $generateTree = false, $maxDepth = 0) {
        //If generateTree is true, generate the folder tree
        if ($generateTree) {
            //Get the folder tree
            $folderTree = $this->buildFolderTree($folderView, $maxDepth);
            //Render the folder heirarchy
            return $this->container->get('templating')->render(
                'MESDJasperReportBundle:Report:folderTree.html.twig', array(
                    'folderTree' => $folderTree,
                )
            );
        } else {
            //Generate the base route for the links
            $route = $this->container->get('request')->get('_route');
            $routeHelper = $this->container->get('templating.helper.router');
            $folderLinks = array();
            //Get the parent folder uri
            reset($folderView);
            $parent = key($folderView);

            //Generate link for parent folder
            if ($parent) {
                if ($stopAtDefault 
                    && strpos($parent, $this->container->get('mesd.jasperreport.client')->getReportDefaultFolder()) === false) {
                    $folderLinks[0] = '#';
                    $parentFolder = $this->container->get('mesd.jasperreport.client')->getReportDefaultFolder();
                } else {
                    $folderLinks[0] = $routeHelper->generate($route, array('folderUri' => $parent));
                    $parentFolder = $parent;
                }
            } else {
                $folderLinks[0] = '#';
                $parentFolder = $this->container->get('mesd.jasperreport.client')->getReportDefaultFolder();
            }

            //Generate links for sub folders
            foreach($folderView[$parent] as $resource) {
                if ($resource->getWsType() == 'folder') {
                    $folderLinks[$resource->getLabel()] = $routeHelper->generate($route, array('folderUri' => $resource->getUriString()));
                }
            }
            //Render the folder contents
            return $this->container->get('templating')->render(
                'MESDJasperReportBundle:Report:folder.html.twig', array(
                    'folderView'    => $folderView[$parent],
                    'folderLinks'   => $folderLinks,
                    'parentFolder'  => $parentFolder,
                )
            );
        }
    }

    //Recursive function to generate an array representing a folder tree
    //folderView is the array given from the clients getFolder call
    //maxDepth is the maximum number of levels to go down (or 0 for all)
    //if maxDepth is set, it will ignore folders on the bottom level
    public function buildFolderTree($folderView, $maxDepth) {
        $reports = array();
        $folders = array();
        foreach($folderView as $resource) {
            if ($resource->getWsType() == 'folder') {
                if (($maxDepth - 1) != 0 ) {
                    $folderCollection = $this->container->get('mesd.jasperreport.client')->getFolder($resource->getUriString());
                    $folders[$resource->getLabel()] = $this->buildFolderTree($folderCollection, $maxDepth - 1);
                }
            } else {
                $reports[$resource->getLabel()] = $resource;
            }
        }
        return array('Folders' => $folders, 'Reports' => $reports);
    }
}