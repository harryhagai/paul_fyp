<?php

namespace App\Http\Controllers;

use App\Models\PageContent;

class PageController extends Controller
{
    public function about()
    {
        $pageContent = PageContent::findPublishedOrDefault('about');
        $pageSettings = $pageContent->mergedSettings();

        return view('about', compact('pageContent', 'pageSettings'));
    }

    public function contact()
    {
        $pageContent = PageContent::findPublishedOrDefault('contact');
        $pageSettings = $pageContent->mergedSettings();

        return view('contact', compact('pageContent', 'pageSettings'));
    }
}
