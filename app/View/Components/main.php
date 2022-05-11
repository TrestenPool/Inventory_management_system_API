<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Log;

class main extends Component
{
    public $title;
    public $jsFiles;
    public $cssFiles;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($title, $jsFiles='no_files', $cssFiles='no_files')
    {
      $this->title = $title;
      $this->jsFiles = $jsFiles;
      $this->cssFiles = $cssFiles;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.main');
    }

}
