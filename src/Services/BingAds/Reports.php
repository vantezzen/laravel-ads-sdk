<?php namespace LaravelAds\Services\BingAds;

use SoapVar;
use SoapFault;
use Exception;
use ZipArchive;

use Microsoft\BingAds\Auth\ServiceClient;
use Microsoft\BingAds\Auth\ServiceClientType;

use Microsoft\BingAds\V12\Reporting\SubmitGenerateReportRequest;
use Microsoft\BingAds\V12\Reporting\PollGenerateReportRequest;
use Microsoft\BingAds\V12\Reporting\AccountPerformanceReportRequest;
use Microsoft\BingAds\V12\Reporting\AudiencePerformanceReportRequest;
use Microsoft\BingAds\V12\Reporting\KeywordPerformanceReportRequest;
use Microsoft\BingAds\V12\Reporting\CampaignPerformanceReportRequest;
use Microsoft\BingAds\V12\Reporting\AdGroupPerformanceReportRequest;
use Microsoft\BingAds\V12\Reporting\SearchQueryPerformanceReportRequest;
use Microsoft\BingAds\V12\Reporting\AgeGenderAudienceReportRequest;
use Microsoft\BingAds\V12\Reporting\GeographicPerformanceReportRequest;
use Microsoft\BingAds\V12\Reporting\DestinationUrlPerformanceReportRequest;
use Microsoft\BingAds\V12\Reporting\ReportFormat;
use Microsoft\BingAds\V12\Reporting\ReportAggregation;
use Microsoft\BingAds\V12\Reporting\AccountThroughAdGroupReportScope;
use Microsoft\BingAds\V12\Reporting\DestinationUrlPerformanceReportColumn;
use Microsoft\BingAds\V12\Reporting\CampaignReportScope;
use Microsoft\BingAds\V12\Reporting\AdGroupReportScope;
use Microsoft\BingAds\V12\Reporting\AccountReportScope;
use Microsoft\BingAds\V12\Reporting\ReportTime;
use Microsoft\BingAds\V12\Reporting\ReportTimePeriod;
use Microsoft\BingAds\V12\Reporting\Date;
use Microsoft\BingAds\V12\Reporting\AccountPerformanceReportFilter;
use Microsoft\BingAds\V12\Reporting\KeywordPerformanceReportFilter;
use Microsoft\BingAds\V12\Reporting\CampaignPerformanceReportFilter;
use Microsoft\BingAds\V12\Reporting\AdGroupPerformanceReportFilter;
use Microsoft\BingAds\V12\Reporting\DeviceTypeReportFilter;
use Microsoft\BingAds\V12\Reporting\AccountPerformanceReportColumn;
use Microsoft\BingAds\V12\Reporting\AudiencePerformanceReportColumn;
use Microsoft\BingAds\V12\Reporting\CampaignPerformanceReportColumn;
use Microsoft\BingAds\V12\Reporting\AdGroupPerformanceReportColumn;
use Microsoft\BingAds\V12\Reporting\KeywordPerformanceReportColumn;
use Microsoft\BingAds\V12\Reporting\SearchQueryPerformanceReportColumn;
use Microsoft\BingAds\V12\Reporting\AgeGenderAudienceReportColumn;
use Microsoft\BingAds\V12\Reporting\GeographicPerformanceReportColumn;
use Microsoft\BingAds\V12\Reporting\ReportRequestStatusType;
use Microsoft\BingAds\V12\Reporting\KeywordPerformanceReportSort;
use Microsoft\BingAds\V12\Reporting\SortOrder;


class Reports
{
    /**
     * $service
     *
     */
    protected $service = null;

    /**
     * $serviceProxy
     *
     */
    protected $serviceProxy = null;

    /**
     * $dateFrom
     *
     */
    protected $dateRange = [];

    /**
     * $fields
     *
     */
    protected $fields = [];

    /**
     * __construct()
     *
     *
     */
    public function __construct($service)
    {
        $this->service = $service;

        $this->serviceProxy = $this->service->call(ServiceClientType::ReportingVersion12);
    }

    /**
     * setDateRange()
     *
     *
     * @return self
     */
    public function setDateRange($dateFrom, $dateTo)
    {
        $this->dateRange[] = $dateFrom;
        $this->dateRange[] = $dateTo;

        return $this;
    }

    /**
     * setFields()
     *
     *
     * @return self
     */
    public function setFields($fields, $auto = false)
    {
        if ($auto == false)
        {
            $this->fields = $fields;
        }

        if ($auto == true && empty($this->fields))
        {
            $this->fields = $fields;
        }

        return $this;
    }


    protected function submitGenerateReport($report)
    {
        $request = new SubmitGenerateReportRequest();
        $request->ReportRequest = $report;

        return $this->serviceProxy->GetService()->SubmitGenerateReport($request);
    }


    /**
     * buildAccountReport()
     *
     *
     */
    public function buildAccountReport()
    {
        $report                         = new AccountPerformanceReportRequest();
        $report->ReportName             = 'Account Performance Report';
        $report->Format                 = ReportFormat::Csv;
        $report->ReturnOnlyCompleteData = false;
        $report->Aggregation            = ReportAggregation::Daily;

        $report->Scope                  = new AccountReportScope();
        $report->Scope->AccountIds      = [$this->service->getClientId()];

        $report->Time                               = new ReportTime();
        $report->Time->CustomDateRangeStart         = new Date();
        $report->Time->CustomDateRangeStart->Day    = date('d',strtotime($this->dateRange[0]));
        $report->Time->CustomDateRangeStart->Month  = date('m',strtotime($this->dateRange[0]));
        $report->Time->CustomDateRangeStart->Year   = date('Y',strtotime($this->dateRange[0]));

        $report->Time->CustomDateRangeEnd           = new Date();
        $report->Time->CustomDateRangeEnd->Day      = date('d',strtotime($this->dateRange[1]));
        $report->Time->CustomDateRangeEnd->Month    = date('m',strtotime($this->dateRange[1]));
        $report->Time->CustomDateRangeEnd->Year     = date('Y',strtotime($this->dateRange[1]));

        $report->Columns = array (
            AccountPerformanceReportColumn::TimePeriod,
            AccountPerformanceReportColumn::AccountId,
            AccountPerformanceReportColumn::Clicks,
            AccountPerformanceReportColumn::Impressions,
            AccountPerformanceReportColumn::Spend,
            AccountPerformanceReportColumn::Conversions,
            AccountPerformanceReportColumn::Revenue
        );

        $encodedReport   = new SoapVar($report, SOAP_ENC_OBJECT, 'AccountPerformanceReportRequest', $this->serviceProxy->GetNamespace());
        $reportRequestId = $this->submitGenerateReport($encodedReport)->ReportRequestId;

        return (new ReportDownload($this->serviceProxy, $reportRequestId));
    }


    /**
     * buildCampaignReport()
     *
     *
     */
    public function buildCampaignReport()
    {
        $report                         = new CampaignPerformanceReportRequest();
        $report->ReportName             = 'Campaign Performance Report';
        $report->Format                 = ReportFormat::Csv;
        $report->ReturnOnlyCompleteData = false;
        $report->Aggregation            = ReportAggregation::Daily;

        $report->Scope                  = new CampaignReportScope();
        $report->Scope->AccountIds      = [$this->service->getClientId()];

        $report->Time                               = new ReportTime();
        $report->Time->CustomDateRangeStart         = new Date();
        $report->Time->CustomDateRangeStart->Day    = date('d',strtotime($this->dateRange[0]));
        $report->Time->CustomDateRangeStart->Month  = date('m',strtotime($this->dateRange[0]));
        $report->Time->CustomDateRangeStart->Year   = date('Y',strtotime($this->dateRange[0]));

        $report->Time->CustomDateRangeEnd           = new Date();
        $report->Time->CustomDateRangeEnd->Day      = date('d',strtotime($this->dateRange[1]));
        $report->Time->CustomDateRangeEnd->Month    = date('m',strtotime($this->dateRange[1]));
        $report->Time->CustomDateRangeEnd->Year     = date('Y',strtotime($this->dateRange[1]));

        $report->Columns = array (
            CampaignPerformanceReportColumn::TimePeriod,
            CampaignPerformanceReportColumn::AccountId,
            CampaignPerformanceReportColumn::CampaignName,
            CampaignPerformanceReportColumn::CampaignId,
            CampaignPerformanceReportColumn::Clicks,
            CampaignPerformanceReportColumn::Impressions,
            CampaignPerformanceReportColumn::Spend,
            CampaignPerformanceReportColumn::Conversions,
            CampaignPerformanceReportColumn::Revenue
        );

        $encodedReport   = new SoapVar($report, SOAP_ENC_OBJECT, 'CampaignPerformanceReportRequest', $this->serviceProxy->GetNamespace());
        $reportRequestId = $this->submitGenerateReport($encodedReport)->ReportRequestId;

        return (new ReportDownload($this->serviceProxy, $reportRequestId));
    }


    /**
     * buildCampaignReport()
     *
     *
     */
    public function buildAdGroupReport()
    {
        $report                         = new AdGroupPerformanceReportRequest();
        $report->ReportName             = 'Ad Group Performance Report';
        $report->Format                 = ReportFormat::Csv;
        $report->ReturnOnlyCompleteData = false;
        $report->Aggregation            = ReportAggregation::Daily;

        $report->Scope                  = new AdGroupReportScope();
        $report->Scope->AccountIds      = [$this->service->getClientId()];

        $report->Time                               = new ReportTime();
        $report->Time->CustomDateRangeStart         = new Date();
        $report->Time->CustomDateRangeStart->Day    = date('d',strtotime($this->dateRange[0]));
        $report->Time->CustomDateRangeStart->Month  = date('m',strtotime($this->dateRange[0]));
        $report->Time->CustomDateRangeStart->Year   = date('Y',strtotime($this->dateRange[0]));

        $report->Time->CustomDateRangeEnd           = new Date();
        $report->Time->CustomDateRangeEnd->Day      = date('d',strtotime($this->dateRange[1]));
        $report->Time->CustomDateRangeEnd->Month    = date('m',strtotime($this->dateRange[1]));
        $report->Time->CustomDateRangeEnd->Year     = date('Y',strtotime($this->dateRange[1]));

        $report->Columns = array (
            AdGroupPerformanceReportColumn::TimePeriod,
            AdGroupPerformanceReportColumn::AccountId,
            AdGroupPerformanceReportColumn::CampaignId,
            AdGroupPerformanceReportColumn::CampaignName,
            AdGroupPerformanceReportColumn::AdGroupId,
            AdGroupPerformanceReportColumn::AdGroupName,
            AdGroupPerformanceReportColumn::Clicks,
            AdGroupPerformanceReportColumn::Impressions,
            AdGroupPerformanceReportColumn::Spend,
            AdGroupPerformanceReportColumn::Conversions,
            AdGroupPerformanceReportColumn::Revenue,
            AdGroupPerformanceReportColumn::AveragePosition
        );

        $encodedReport   = new SoapVar($report, SOAP_ENC_OBJECT, 'AdGroupPerformanceReportRequest', $this->serviceProxy->GetNamespace());
        $reportRequestId = $this->submitGenerateReport($encodedReport)->ReportRequestId;

        return (new ReportDownload($this->serviceProxy, $reportRequestId));
    }


    /**
     * buildFinalUrlReport()
     *
     *
     */
    public function buildFinalUrlReport()
    {
        try
        {
            $report                         = new DestinationUrlPerformanceReportRequest();
            $report->ReportName             = 'Destination Url Performance Report';
            $report->Format                 = ReportFormat::Csv;
            $report->ReturnOnlyCompleteData = false;
            $report->Aggregation            = ReportAggregation::Daily;

            $report->Scope                  = new AccountThroughAdGroupReportScope();
            $report->Scope->AccountIds      = [$this->service->getClientId()];

            $report->Time                               = new ReportTime();
            $report->Time->CustomDateRangeStart         = new Date();
            $report->Time->CustomDateRangeStart->Day    = date('d',strtotime($this->dateRange[0]));
            $report->Time->CustomDateRangeStart->Month  = date('m',strtotime($this->dateRange[0]));
            $report->Time->CustomDateRangeStart->Year   = date('Y',strtotime($this->dateRange[0]));

            $report->Time->CustomDateRangeEnd           = new Date();
            $report->Time->CustomDateRangeEnd->Day      = date('d',strtotime($this->dateRange[1]));
            $report->Time->CustomDateRangeEnd->Month    = date('m',strtotime($this->dateRange[1]));
            $report->Time->CustomDateRangeEnd->Year     = date('Y',strtotime($this->dateRange[1]));

            $report->Columns = array (
                DestinationUrlPerformanceReportColumn::TimePeriod,
                DestinationUrlPerformanceReportColumn::AccountName,
                DestinationUrlPerformanceReportColumn::AccountId,
                DestinationUrlPerformanceReportColumn::CampaignId,
                DestinationUrlPerformanceReportColumn::CampaignName,
                DestinationUrlPerformanceReportColumn::Clicks,
                DestinationUrlPerformanceReportColumn::Impressions,
                DestinationUrlPerformanceReportColumn::Spend,
                DestinationUrlPerformanceReportColumn::Conversions,
                DestinationUrlPerformanceReportColumn::Revenue,
                DestinationUrlPerformanceReportColumn::DestinationUrl,
                DestinationUrlPerformanceReportColumn::FinalUrl
            );

            $encodedReport   = new SoapVar($report, SOAP_ENC_OBJECT, 'DestinationUrlPerformanceReportRequest', $this->serviceProxy->GetNamespace());
            $reportRequestId = $this->submitGenerateReport($encodedReport)->ReportRequestId;
        }
        catch (SoapFault $e)
        {
        	printf("-----\r\nFault Code: %s\r\nFault String: %s\r\nFault Detail: \r\n", $e->faultcode, $e->faultstring);
            var_dump($e->detail);
        	print "-----\r\nLast SOAP request/response:\r\n";
            print $this->serviceProxy->GetWsdl() . "\r\n";
        	print $this->serviceProxy->__getLastRequest()."\r\n";
            print $this->serviceProxy->__getLastResponse()."\r\n";
        }

        return (new ReportDownload($this->serviceProxy, $reportRequestId));
    }


    /**
     * buildFinalUrlReport()
     *
     *
     */
    public function buildSearchTermReport()
    {
        try
        {
            $report                         = new SearchQueryPerformanceReportRequest();
            $report->ReportName             = 'Search Query Performance Report';
            $report->Format                 = ReportFormat::Csv;
            $report->ReturnOnlyCompleteData = false;
            $report->Aggregation            = ReportAggregation::Daily;

            $report->Scope                  = new AccountThroughAdGroupReportScope();
            $report->Scope->AccountIds      = [$this->service->getClientId()];

            $report->Time                               = new ReportTime();
            $report->Time->CustomDateRangeStart         = new Date();
            $report->Time->CustomDateRangeStart->Day    = date('d',strtotime($this->dateRange[0]));
            $report->Time->CustomDateRangeStart->Month  = date('m',strtotime($this->dateRange[0]));
            $report->Time->CustomDateRangeStart->Year   = date('Y',strtotime($this->dateRange[0]));

            $report->Time->CustomDateRangeEnd           = new Date();
            $report->Time->CustomDateRangeEnd->Day      = date('d',strtotime($this->dateRange[1]));
            $report->Time->CustomDateRangeEnd->Month    = date('m',strtotime($this->dateRange[1]));
            $report->Time->CustomDateRangeEnd->Year     = date('Y',strtotime($this->dateRange[1]));

            $report->Columns = array (
                SearchQueryPerformanceReportColumn::TimePeriod,
                SearchQueryPerformanceReportColumn::Clicks,
                SearchQueryPerformanceReportColumn::Impressions,
                SearchQueryPerformanceReportColumn::Spend,
                SearchQueryPerformanceReportColumn::Conversions,
                SearchQueryPerformanceReportColumn::Revenue,
                SearchQueryPerformanceReportColumn::SearchQuery
            );

            $encodedReport   = new SoapVar($report, SOAP_ENC_OBJECT, 'SearchQueryPerformanceReportRequest', $this->serviceProxy->GetNamespace());
            $reportRequestId = $this->submitGenerateReport($encodedReport)->ReportRequestId;
        }
        catch (SoapFault $e)
        {
        	printf("-----\r\nFault Code: %s\r\nFault String: %s\r\nFault Detail: \r\n", $e->faultcode, $e->faultstring);
            var_dump($e->detail);
        	print "-----\r\nLast SOAP request/response:\r\n";
            print $this->serviceProxy->GetWsdl() . "\r\n";
        	print $this->serviceProxy->__getLastRequest()."\r\n";
            print $this->serviceProxy->__getLastResponse()."\r\n";
        }

        return (new ReportDownload($this->serviceProxy, $reportRequestId));
    }


    /**
     * buildAgeRangeReport()
     *
     *
     */
    public function buildAgeGenderReport()
    {
        try
        {
            $report                         = new AgeGenderAudienceReportRequest();
            $report->ReportName             = 'Age Gender Performance Report';
            $report->Format                 = ReportFormat::Csv;
            $report->ReturnOnlyCompleteData = false;
            $report->Aggregation            = ReportAggregation::Daily;

            $report->Scope                  = new AccountThroughAdGroupReportScope();
            $report->Scope->AccountIds      = [$this->service->getClientId()];

            $report->Time                               = new ReportTime();
            $report->Time->CustomDateRangeStart         = new Date();
            $report->Time->CustomDateRangeStart->Day    = date('d',strtotime($this->dateRange[0]));
            $report->Time->CustomDateRangeStart->Month  = date('m',strtotime($this->dateRange[0]));
            $report->Time->CustomDateRangeStart->Year   = date('Y',strtotime($this->dateRange[0]));

            $report->Time->CustomDateRangeEnd           = new Date();
            $report->Time->CustomDateRangeEnd->Day      = date('d',strtotime($this->dateRange[1]));
            $report->Time->CustomDateRangeEnd->Month    = date('m',strtotime($this->dateRange[1]));
            $report->Time->CustomDateRangeEnd->Year     = date('Y',strtotime($this->dateRange[1]));

            $report->Columns = array (
                AgeGenderAudienceReportColumn::TimePeriod,
                AgeGenderAudienceReportColumn::AccountName,
                AgeGenderAudienceReportColumn::AdGroupName,
                AgeGenderAudienceReportColumn::AgeGroup,
                AgeGenderAudienceReportColumn::Gender,
                AgeGenderAudienceReportColumn::Clicks,
                AgeGenderAudienceReportColumn::Impressions,
                AgeGenderAudienceReportColumn::Spend,
                AgeGenderAudienceReportColumn::Conversions,
                AgeGenderAudienceReportColumn::Revenue
            );

            $encodedReport   = new SoapVar($report, SOAP_ENC_OBJECT, 'AgeGenderAudienceReportRequest', $this->serviceProxy->GetNamespace());
            $reportRequestId = $this->submitGenerateReport($encodedReport)->ReportRequestId;
        }
        catch (SoapFault $e)
        {
        	printf("-----\r\nFault Code: %s\r\nFault String: %s\r\nFault Detail: \r\n", $e->faultcode, $e->faultstring);
            var_dump($e->detail);
        	print "-----\r\nLast SOAP request/response:\r\n";
            print $this->serviceProxy->GetWsdl() . "\r\n";
        	print $this->serviceProxy->__getLastRequest()."\r\n";
            print $this->serviceProxy->__getLastResponse()."\r\n";
        }

        return (new ReportDownload($this->serviceProxy, $reportRequestId));
    }


    /**
     * buildGeoReport()
     *
     *
     */
    public function buildMostSpecificLocationReport()
    {
        try
        {
            $report                         = new GeographicPerformanceReportRequest();
            $report->ReportName             = 'Age Gender Performance Report';
            $report->Format                 = ReportFormat::Csv;
            $report->ReturnOnlyCompleteData = false;
            $report->Aggregation            = ReportAggregation::Summary;

            $report->Scope                  = new AccountThroughAdGroupReportScope();
            $report->Scope->AccountIds      = [$this->service->getClientId()];

            $report->Time                               = new ReportTime();
            $report->Time->CustomDateRangeStart         = new Date();
            $report->Time->CustomDateRangeStart->Day    = date('d',strtotime($this->dateRange[0]));
            $report->Time->CustomDateRangeStart->Month  = date('m',strtotime($this->dateRange[0]));
            $report->Time->CustomDateRangeStart->Year   = date('Y',strtotime($this->dateRange[0]));

            $report->Time->CustomDateRangeEnd           = new Date();
            $report->Time->CustomDateRangeEnd->Day      = date('d',strtotime($this->dateRange[1]));
            $report->Time->CustomDateRangeEnd->Month    = date('m',strtotime($this->dateRange[1]));
            $report->Time->CustomDateRangeEnd->Year     = date('Y',strtotime($this->dateRange[1]));

            $report->Columns = array (
                GeographicPerformanceReportColumn::AccountName,
                GeographicPerformanceReportColumn::LocationType,
                GeographicPerformanceReportColumn::MostSpecificLocation,
                GeographicPerformanceReportColumn::Country,
                GeographicPerformanceReportColumn::State,
                GeographicPerformanceReportColumn::MetroArea,
                GeographicPerformanceReportColumn::City,
                GeographicPerformanceReportColumn::County,
                GeographicPerformanceReportColumn::PostalCode,
                GeographicPerformanceReportColumn::LocationId,
                GeographicPerformanceReportColumn::Clicks,
                GeographicPerformanceReportColumn::Impressions,
                GeographicPerformanceReportColumn::Spend,
                GeographicPerformanceReportColumn::Conversions,
                GeographicPerformanceReportColumn::Revenue
            );

            $encodedReport   = new SoapVar($report, SOAP_ENC_OBJECT, 'GeographicPerformanceReportRequest', $this->serviceProxy->GetNamespace());
            $reportRequestId = $this->submitGenerateReport($encodedReport)->ReportRequestId;
        }
        catch (SoapFault $e)
        {
        	printf("-----\r\nFault Code: %s\r\nFault String: %s\r\nFault Detail: \r\n", $e->faultcode, $e->faultstring);
            var_dump($e->detail);
        	print "-----\r\nLast SOAP request/response:\r\n";
            print $this->serviceProxy->GetWsdl() . "\r\n";
        	print $this->serviceProxy->__getLastRequest()."\r\n";
            print $this->serviceProxy->__getLastResponse()."\r\n";
        }

        return (new ReportDownload($this->serviceProxy, $reportRequestId));
    }




    /**
     * getAccountReport()
     *
     *
     */
    public function getAccountReport()
    {
        return $this->buildAccountReport()->toCollection();
    }


    /**
     * getCampaignReport()
     *
     *
     */
    public function getCampaignReport()
    {
        return $this->buildCampaignReport()->toCollection();
    }


    /**
     * getAdGroupReport()
     *
     *
     */
    public function getAdGroupReport()
    {
        return $this->buildAdGroupReport()->toCollection();
    }


    /**
     * getDestinationUrlReport()
     *
     *
     */
    public function getFinalUrlReport()
    {
        return $this->buildFinalUrlReport()->toCollection();
    }


    /**
     * getDestinationUrlReport()
     *
     *
     */
    public function getSearchTermReport()
    {
        return $this->buildSearchTermReport()->aggregate('search_term');
    }


    /**
     * getAgeRangeReport()
     *
     *
     */
    public function getAgeRangeReport()
    {
        return $this->buildAgeGenderReport()->aggregate('age_range');
    }


    /**
     * getGenderReport()
     *
     *
     */
    public function getGenderReport()
    {
        return $this->buildAgeGenderReport()->aggregate('gender');
    }


    /**
     * getMostSpecificLocationReport()
     *
     *
     */
    public function getMostSpecificLocationReport()
    {
        return $this->buildMostSpecificLocationReport()->toCollection();
    }

}
