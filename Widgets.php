<?php

namespace Apps\Fintech\Components\Etf\Portfolios;

use Apps\Fintech\Packages\Etf\Portfolios\EtfPortfolios;
use Carbon\Carbon;
use System\Base\Providers\ModulesServiceProvider\Modules\Components\ComponentsWidgets;

class Widgets extends ComponentsWidgets
{
    protected $portfoliosPackage;

    public function init($componentObj, $component)
    {
        $this->portfoliosPackage = new EtfPortfolios;

        return parent::init($componentObj, $component);
    }

    public function etfPortfolio($widget, $dashboardWidget)
    {
        if (isset($dashboardWidget['getWidgetData'])) {
            $this->getAccountPortfolios($widget);

            if (isset($dashboardWidget['settings']['portfolios'])) {
                $widgetPortfolio = $this->portfoliosPackage->getPortfolioById((int) $dashboardWidget['settings']['portfolios']);

                if ($widgetPortfolio) {
                    $dashboardWidget['settings']['portfolios'] = [];
                    $dashboardWidget['settings']['portfolios']['id'] = $widgetPortfolio['id'];
                    $dashboardWidget['settings']['portfolios']['name'] = $widgetPortfolio['name'];
                    $dashboardWidget['settings']['portfolios']['status'] = $widgetPortfolio['status'];
                    $dashboardWidget['settings']['portfolios']['invested_amount'] =
                        str_replace('EN_ ',
                                '',
                                (new \NumberFormatter('en_AU', \NumberFormatter::CURRENCY))
                                    ->formatCurrency($widgetPortfolio['invested_amount'], 'en_AU'));
                    $dashboardWidget['settings']['portfolios']['return_amount'] =
                        str_replace('EN_ ',
                                '',
                                (new \NumberFormatter('en_AU', \NumberFormatter::CURRENCY))
                                    ->formatCurrency($widgetPortfolio['return_amount'], 'en_AU'));
                    $dashboardWidget['settings']['portfolios']['xirr'] = $widgetPortfolio['xirr'];
                    $dashboardWidget['settings']['portfolios']['sold_amount'] =
                        str_replace('EN_ ',
                                '',
                                (new \NumberFormatter('en_AU', \NumberFormatter::CURRENCY))
                                    ->formatCurrency($widgetPortfolio['sold_amount'], 'en_AU'));
                    $dashboardWidget['settings']['portfolios']['total_value'] =
                        str_replace('EN_ ',
                                '',
                                (new \NumberFormatter('en_AU', \NumberFormatter::CURRENCY))
                                    ->formatCurrency($widgetPortfolio['total_value'], 'en_AU'));
                    $dashboardWidget['settings']['portfolios']['profit_loss'] =
                        str_replace('EN_ ',
                                '',
                                (new \NumberFormatter('en_AU', \NumberFormatter::CURRENCY))
                                    ->formatCurrency($widgetPortfolio['profit_loss'], 'en_AU'));
                }
            }
        }

        return $this->getWidgetContent($widget, $dashboardWidget);
    }

    public function etfPortfolios($widget, $dashboardWidget)
    {
        if (isset($dashboardWidget['getWidgetData'])) {
            $this->getAccountPortfolios($widget);

            if (isset($dashboardWidget['settings']['portfolios']) &&
                is_array($dashboardWidget['settings']['portfolios']) &&
                count($dashboardWidget['settings']['portfolios']) > 0
            ) {
                $portfoliosNames = [];
                $portfoliosIds = [];
                $widgetPortfolioInvestedAmount = 0;
                $widgetPortfolioReturnAmount = 0;
                $widgetPortfolioXirr = 0;
                $widgetPortfolioSoldAmount = 0;
                $widgetPortfolioTotalValue = 0;
                $widgetPortfolioProfitLoss = 0;

                foreach ($dashboardWidget['settings']['portfolios'] as $widgetPortfolio) {
                    $widgetPortfolio = $this->portfoliosPackage->getPortfolioById((int) $widgetPortfolio);

                    if ($widgetPortfolio) {
                        array_push($portfoliosNames, $widgetPortfolio['name']);
                        array_push($portfoliosIds, $widgetPortfolio['id']);

                        $widgetPortfolioInvestedAmount += $widgetPortfolio['invested_amount'];
                        $widgetPortfolioReturnAmount += $widgetPortfolio['return_amount'];
                        $widgetPortfolioXirr += $widgetPortfolio['xirr'];
                        $widgetPortfolioSoldAmount += $widgetPortfolio['sold_amount'];
                        $widgetPortfolioTotalValue += $widgetPortfolio['total_value'];
                        $widgetPortfolioProfitLoss += $widgetPortfolio['profit_loss'];
                    }
                }

                $dashboardWidget['settings']['portfolios'] = [];
                $dashboardWidget['settings']['portfolios']['id'] = $portfoliosIds;
                $dashboardWidget['settings']['portfolios']['name'] = implode(',', $portfoliosNames);
                if ($widgetPortfolioProfitLoss > 0) {
                    $dashboardWidget['settings']['portfolios']['status'] = 'positive';
                } else if ($widgetPortfolioProfitLoss < 0) {
                    $dashboardWidget['settings']['portfolios']['status'] = 'negative';
                } else if ($widgetPortfolioProfitLoss == 0) {
                    $dashboardWidget['settings']['portfolios']['status'] = 'neutral';
                }
                $dashboardWidget['settings']['portfolios']['invested_amount'] =
                    str_replace('EN_ ',
                            '',
                            (new \NumberFormatter('en_AU', \NumberFormatter::CURRENCY))
                                ->formatCurrency($widgetPortfolioInvestedAmount, 'en_AU'));
                $dashboardWidget['settings']['portfolios']['return_amount'] =
                    str_replace('EN_ ',
                            '',
                            (new \NumberFormatter('en_AU', \NumberFormatter::CURRENCY))
                                ->formatCurrency($widgetPortfolioReturnAmount, 'en_AU'));
                $dashboardWidget['settings']['portfolios']['xirr'] = $widgetPortfolioXirr;
                $dashboardWidget['settings']['portfolios']['sold_amount'] =
                    str_replace('EN_ ',
                            '',
                            (new \NumberFormatter('en_AU', \NumberFormatter::CURRENCY))
                                ->formatCurrency($widgetPortfolioSoldAmount, 'en_AU'));
                $dashboardWidget['settings']['portfolios']['total_value'] =
                    str_replace('EN_ ',
                            '',
                            (new \NumberFormatter('en_AU', \NumberFormatter::CURRENCY))
                                ->formatCurrency($widgetPortfolioTotalValue, 'en_AU'));
                $dashboardWidget['settings']['portfolios']['profit_loss'] =
                    str_replace('EN_ ',
                            '',
                            (new \NumberFormatter('en_AU', \NumberFormatter::CURRENCY))
                                ->formatCurrency($widgetPortfolioProfitLoss, 'en_AU'));
            }
        }

        return $this->getWidgetContent($widget, $dashboardWidget);
    }

    protected function getAccountPortfolios(&$widget)
    {
        $accountPortfolios = $this->portfoliosPackage->getPortfoliosByAccountId();

        if ($accountPortfolios) {
            $portfolios = [];

            if ($accountPortfolios && count($accountPortfolios) > 0) {
                foreach ($accountPortfolios as $portfolio) {
                    $portfolios[$portfolio['id']] = [];
                    $portfolios[$portfolio['id']]['id'] = $portfolio['id'];
                    $portfolios[$portfolio['id']]['name'] = $portfolio['name'];
                }
            }

            $widget['settings']['portfolios'] = $portfolios;
        }
    }
}