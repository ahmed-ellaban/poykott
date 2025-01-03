<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\ContactMessage;
use App\Models\SimilarSiteCategory;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $companies = Company::query()
            ->with([
                'media',
                'tagsRelation' => function ($query): void {
                    $query->select('tags.id', 'name')->limit(3);
                },
            ])
            ->approved()
            ->paginate(20, ['companies.id', 'name', 'description', 'slug', 'image_path']);

        return view('home', ['companies' => $companies]);
    }

    public function loadMore(Request $request)
    {
        $companies = Company::with([
            'media',
            'tagsRelation' => function ($query): void {
                $query->select('tags.id', 'name')->limit(3);
            },
        ])
            ->approved()
            ->paginate(20, ['companies.id', 'name', 'description', 'slug']);

        $companies->getCollection()->transform(function ($company) {
            $company->image_path = $company->imagePath;

            return $company;
        });

        return response()->json(['companies' => $companies]);
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $companies = Company::query()
            ->with([
                'media',
                'tagsRelation' => function ($query): void {
                    $query->select('tags.id', 'name')->limit(3);
                },
            ])
            ->approved()
            ->where('name', 'like', "%{$search}%")
            ->orWhere('description', 'like', "%{$search}%")
            ->paginate(40, ['companies.id', 'name', 'description', 'slug']);

        $companies->getCollection()->transform(function ($company) {
            $company->image_path = $company->imagePath;

            return $company;
        });

        return response()->json(['companies' => $companies]);
    }

    public function about()
    {
        return view('pages.about');
    }

    public function contact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'required|string',
        ]);

        ContactMessage::query()->create($validated);

        return back()->with('success', 'Message sent successfully');
    }

    public function similarSites()
    {
        $similarSitesCategories = SimilarSiteCategory::query()->with('similarSites')->get();

        return view('pages.similar-sites', ['similarSitesCategories' => $similarSitesCategories]);
    }
}
