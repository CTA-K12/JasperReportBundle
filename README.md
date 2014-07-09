Overview
========
The Jasper Report Bundle serves as a layer on top of the vanilla PHP MESD Jasper Client library to make it easier to integrate with Symfony applications.  The bundle also contains extra features on top of the client library that make use of some of Symfon'y features and to provide extra tools when adding Jasper Report support to an application.  Also, the report bundle is built to be compatible with an optional report viewer bundle that provides the views and controller to handle most of the common report bundle.

###Features
The report bundle and the client currently offer the following features:
- Ability to get a list of reports from the Jasper Server
- Report execution, with report output being saved to a local directory for later retrieval
- Input control option management that makes it easy to lock down options for particular input controls based on the user
- Converts report input to Symfony forms
- Save and retrieve past reports

###Todo List
- Add support for Jasper Server Pro, currently only the community version is supported
- Add more tools to help with managing the report store

Installation
============
Add Packages to Project via Composer
For the first step, both the Jasper Client and the Jasper Report Bundle must be added to the project's composer.json file.  
```javascript
"repositories": [
        {
            "type" : "vcs",
            "url" : "https://github.com/MESD/JasperClient.git"
        },
        {
            "type" : "vcs",
            "url" : "https://github.com/MESD/JasperReportBundle.git"
        }
    ],
    "require": {
        "mesd/jasper-client": "1.0.0-alpha+001",
        "mesd/jasper-report-bundle": "1.0.0-alpha+001"
    }
```
After these have been added, perform a composer update to install the packages in your vendor directory.
Register the Bundle in the App Kernel
As with all other bundles, the report bundle will need to be registered in the application's app kernel.  
```php
public function registerBundles()
{
    $bundles = array(
        new Mesd\Jasper\ReportBundle\MesdJasperReportBundle()
    )
} 
```

###Connecting to the Jasper Report Server
The next step is to add in the connection details to the config file so that the bundle can establish a connection with the report server
```yaml
#config.yml
mesd_jasper_report:
    connection:
        username: %jasper_username%
        password: %jasper_password%
        host: %jasper_host%
        port: %jasper_port%
```

Link to the Bundle's Routing File
The report bundle uses some internal routes to handle the processing of report assets (images, charts, and graphs) and for exporting reports saved in the report store.  Since Symfony's autoloader does not handle routing automatically, you must import the bundle's routing.yml file for it to function.  To do so, add the following to your routing.yml in the app/config directory.
```yaml
MESDJasperReportBundle:
    resource: "@MesdJasperReportBundle/Resources/config/routing.yml"
    prefix: /reportbundle
```

###Setting the Configuration Options
Finally, all that is left is to set the configuration options for the report bundle.  Most options have a default value, and for now there is only required option that needs to be set to get going, and that is just giving the bundle's configuration the name of the options handler service that was set up in the last step.  Visit the configuration section later to get a list of the configuration settings the report bundle has.

Usage
=====

###Creating an Options Handler
To allow application specific control of the options for select parameters, an options handler service can be created to handle this.  To create a basic option handler, extend the AbstractOptionsHandler class from Mesd\Jasper\ReportBundle\Interfaces\AbstractOptionsHandler and implement the method 'registerFunctions' that set the internal method  an array that is a function that returns an array of options keyed by the input control id.  For example:
```php
<?php
 
namespace My\Bundle\Somewhere;
 
use Mesd\Jasper\ReportBundle\Interfaces\AbstractOptionsHandler;
use Mesd\Jasper\ReportBundle\InputControl\Option;
 
class MyOptionsHandler extends AbstractOptionsHander
{
    public function registerFunctions() {
        return array(
            'someInputControlId' => 'foo'
        );
    }
 
    public function foo() {
        return array(
            new Option('1', 'I want door number 1'),
            new Option('2', 'I really want door number 2'),
            new Option('3', 'Just give me what ever is behind door number 3')
        );
    }
 
}
```
Once the class is created, it needs to be made into a service in service configuration file which will look something like the following
```yaml
parameters:
    my_options_handler: My\Bundle\Somewhere\MyOptionsHandler
 
services:
    my_options_handler:
        class: %my_options_handler%
```

###Getting a List of Resources from the Report Server
To get a list of resources from the report server, use the client service method getResourceList to return an array of resource objects.  If report security is on, this will only return resources the current user has the roles to view.
Resource List
```php
//Get a list of resources in the default directory
$resources = $this->container->get('mesd.jasper.report.client')->getResourceList();
 
//Get a list of resources from a particular directory
$resources = $this->container->get('mesd.jasper.report.client')->('reports/super-secret-reports/');
 
//Get all the resources below a particular directory (note that this can be a slow process)
$resources = $this->container->get('mesd.jasper.report.client')->('reports/', true);
```

###Getting the Input Controls
To build a new report, we need to first get the input controls for a report and display them.  This can be easily done through the bundle which will build a Symfony form from the input controls of a report and will make use of the options handler if the input option source is set to 'Custom' or 'Fallback' mode.  Example follows:
```php
//Display the report form
public function formAction()
{
    $form = $this->container->get('mesd.jasper.report.client')->buildReportInputForm('report/sales/quarterly_report', 'route_to_next_action', array('routeParameters' => array('key' => 'value)));
 
    //Display and such
    ...
}
```

###Running a Report
Once the input controls have been filled out, they need to be given back to the report and then we can run the report.  When the report is run via the report builder's runReport method, the report will be stored into the report store directory (in all of its requested formats, which by default are html, pdf, and xls) and a record entry will be inserted into the database.  Each instance of a report have a request id that serve as their unique identifier.  Example follows:
```php
//Run the report and get the output
public function processAction(Request $request, $key) {
    //Note that key will be 'value' here (just in case you need something like this)
     
    //Rebuild the form
    $form = $this->container->get('mesd.jasper.report.client')->buildReportInputForm('report/sales/quarterly_report');
  
    //Handle the request
    $form->handleRequest($request);
  
    //Check if the form is valid
    if ($form->isValid()) {
        //Create the report builder
        $rb = $this->container->get('mesd.jasper.report.client')->createReportBuilder('report/sales/quarterly_report');
        $rb->setInputParametersArray($form->getData());
 
 
        //Run the report and get the request id (NOTE: this instance of the report will now be saved to the report store and a record will be saved to the db)
        $requestId = $rb->runReport();
 
    }
}
```
With the request id for the report, it can be loaded from the report store at anytime, either right away for display or brought up later for reference.

###Getting a Report from the Report Store
To get a report from the report store, just call the loader service and give it the request id and the page number to retrieve when displaying html reports
```php
//Get page 1 of the report
 
//Call the loader service to setup the client library's report loader
$reportLoader = $this->container->get('mesd.jasper.report.loader')->getReportLoader();
$report = $reportLoader->getCachedReport($requestId, 'html', array('page' => 1));
 
//Display
$report->getOutput();
```
You can get the report in other formats by changing the html to pdf or xls, though non-html formats do not accept the page option.

###Role Based Report Security
The Report Security Yaml File
By creating a report security yaml and setting the option in the config setting, the client service will automatically limit what reports a user can view based on their roles.  These roles can be set on a directory or report based instance.
```yaml
reports: #the reports directory
    _roles:     # By default only a user with report_user or admin can view the resource in the reports directory
        - ROLE_REPORT_USER
        - ROLE_ADMIN
    project_a: #reports/project_a
        report_cake:    #reports/project_a/report_cake
            _roles: #only admins can see this report
                - ROLE_ADMIN
```
The Jasper Report Bundle includes a command (mesd_jasper_report:security::generate-yaml) that can help in generating a base report security file for you.  Run the following for more information
app/console mesd_jasper_report:security::generate-yaml --help

###Configuration Settings
The report bundle has a wide array of configuration settings.  Following is a list of possible options, their default value, and their functionality.  The default values are represented in brackets([])
```yaml
mesd_jasper_report:
    default_folder: [/reports] #The folder on the jasper server to use as a default
    connection:
        username: [please_change] #Username of the account on the jasper server to use
        password: [please_change] #Password for the above user
        host: [please_change] #The Hostname of the jasper server
        port: [8080] #The port number that the jasper server is listening to
    folder_cache: #Settings for the cached resource list
        use_cache: [true] #Whether to cache resource lists
        cache_dir: [../app/cache/jasper_resource_list/] #Directory to store the resource list cache
        cache_timeout: [30] #Amount of time before the resource list cache expires
    report_cache: #Settings for the report store
        use_cache: [true] Whether to save reports in the report store by default
        cache_dir: [../report-store/reports/] #Report store directory
    display:
        default_export_route: [MesdJasperReportBundle_export_cached_report] #The route that handle report assets by default
    report_loader:
        default_page: [1] #The page to load by default if none given
        default_attach_asset_url: [true] #Whether to handle report assets by default
        default_asset_route: [MesdJasperReportBundle_render_cached_asset] #The route that handle report assets by default
    report_history:
        entity_manager: [default] #The entity manager that will handle report execution records
    options_handler: *REQUIRED* #Service name of the application specific options handler
    default_input_options_source: [Fallback] #Where to get the input options from by default (Fallback, Jasper, or Custom)
    report_security:
        use_security: [true] #Whether to use the security service or not
        max_level_set_at_default: [true] #Prevent any folder higher than the default from being viewed
        security_file: [/config/report_security.yml] #The location of the report security yml file
        default_roles: #Default roles to attach to the security yml when using the generator
            [ -ROLE_USER
              -ROLE_ADMIN
              -ROLE_SUPERADMIN ]
```

###Displaying Input Options Based on User
One of the most common purposes for overriding the input options and having the options handler service is to provide a way to limit options to users, such that a user can only run a report for data that they have permission to view.  The following is an example that limits users to only view reports for their department.
 
```php
<?php
 
namespace My\Bundle\Somewhere;
 
use Mesd\Jasper\ReportBundle\Interfaces\AbstractOptionsHandler;
use Mesd\Jasper\ReportBundle\InputControl\Option;
 
class MyOptionsHandler extends AbstractOptionsHandler
{
    //The Symfony SecurityContext object
    $securityContext;
 
    //Override the constructor to allow the injection of the security context
    public function __construct($securityContext) {
        $this->securityContext = $securityContext;
         
        //Call the parent constructor (this is important)
        parent::__construct()
    }
     
    //Register the functions
    protected function registerFunctions(){
        return array(
            'Deptmartment' => 'getOptionsForDept'
        );
    }
     
    //Get the options for the department selector
    public function getOptionsForDept() {
        $options = array();
        foreach($this->securityContext->getUser()->getDepartments() as $dept) {
            $options[] = new Option($dept->getId(), $dept->getName());
        }
        return $options;
    }
}
```

###Using the Remove Broken Report History Records Command
It's possible over time that the report history records in the database will point to reports that are no longer saved in the store.  The app/console mesd_jasper_report:history:delete_broken_records command will delete all those records that no longer point to a report in the store.  Add the --dry-run command to see which records will try to delete, and then run without to delete them.

API Documentation
=================
Generated documentation exists in the bundle under the docs directory.

License
=======
This project is licensed under the MIT license. See the [LICENSE.md](LICENSE.md) file for more information.
