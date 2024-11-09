<?php

namespace App\Console\Commands;

use App\Enums\ResourceType;
use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportCompaniesOldSiteCommand extends Command
{
    protected $signature = 'import:companies-old-site';

    protected $description = 'Command description';

    public function handle(): void
    {
        $json = file_get_contents(storage_path('app/private/7-israel-companies-services.json'));

        $allData = json_decode($json, true);

        $progressBar = $this->output->createProgressBar(count($allData));

        $companies = data_get($allData, 'companiesAndServices');

        //$r= collect($companies)
        //    ->map(function ($company) {
        //        return $company['resources'][0];
        //    })->groupBy('name');

        //dd($r->keys()->toArray());

        foreach ($companies as $companyData) {

            $lowerName = Str::lower(data_get($companyData, 'name'));

            $company = Company::whereRaw('LOWER(name) = ?', [$lowerName])->first();

            if (is_null($company)) {
                $company = Company::create([
                    'name' => data_get($companyData, 'name'),
                    'description' => data_get($companyData, 'description'),
                    'url' => '#',
                ]);
            }

            if (empty($company->description)) {
                $company->update(['description' => data_get($companyData, 'description')]);
            }

            $resourcesData = data_get($companyData, 'resources');

            foreach ($resourcesData as $resourceData) {

                if (empty(data_get($resourceData, 'name'))) {
                    continue;
                }

                $company->companyResources()->updateOrCreate([
                    'url' => data_get($resourceData, 'link', '#'),
                ], [
                    'title' => match (data_get($resourceData, 'name')) {
                        'Wikipedia' => ResourceType::Wikipedia,
                        'Twitter' => ResourceType::Twitter,
                        'LinkedIn' => ResourceType::LinkedIn,
                        'Wikitia' => ResourceType::Wikitia,
                        'Wikidata' => ResourceType::Wikidata,
                        'golden.com' => ResourceType::Golden,
                        'verify.wiki' => ResourceType::VerifyWiki,
                        'Buy Israeli Tech' => ResourceType::BuyIsraeliTech,
                        'عن الموقع' => ResourceType::OfficialWebsite,
                        'bloomberg' => ResourceType::Bloomberg,
                    },
                ]);

                $resource = $company->resources()->updateOrCreate([
                    'url' => data_get($resourceData, 'link', '#'),
                ], [
                    'type' => match (data_get($resourceData, 'name')) {
                        'Wikipedia' => ResourceType::Wikipedia,
                        'Twitter' => ResourceType::Twitter,
                        'LinkedIn' => ResourceType::LinkedIn,
                        'Wikitia' => ResourceType::Wikitia,
                        'Wikidata' => ResourceType::Wikidata,
                        'golden.com' => ResourceType::Golden,
                        'verify.wiki' => ResourceType::VerifyWiki,
                        'Buy Israeli Tech' => ResourceType::BuyIsraeliTech,
                        'عن الموقع' => ResourceType::OfficialWebsite,
                        'bloomberg' => ResourceType::Bloomberg,
                    },
                ]);
            }

            $alternativesData = data_get($companyData, 'alternatives');

            foreach ($alternativesData as $alternativeData) {
                if (empty($alternativeData['name'])) {
                    continue;
                }
                $company->alternatives()->updateOrCreate([
                    'name' => data_get($alternativeData, 'name'),
                ], [
                    'description' => data_get($alternativeData, 'description'),
                    'url' => data_get($alternativeData, 'url', '#'),
                    'notes' => data_get($alternativeData, 'notes'),
                ]);
            }

            $this->line("Processed importing: {$company->name}");
            $progressBar->advance();

            $progressBar->finish();

            $this->info("\nProcessed Completed!");
        }

    }
}