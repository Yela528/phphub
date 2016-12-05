@extends('layouts.default')

@section('content')

{{--<div class="box text-center site-index rm-link-color">--}}
    {{--<form method="GET" action="{{route('search')}}" accept-charset="UTF-8" class="navbar-form navbar-left" target="_blank">--}}
        {{--<div class="form-group">--}}
            {{--<input class="form-control search-input mac-style" placeholder="{{lang('Search')}}" name="q" type="text">--}}
        {{--</div>--}}
    {{--</form>--}}

{{--</div>--}}

@include('layouts.partials.topbanner')
<div class="sites-index">

    @include('sites.partials.sites', ['heading_title' => '<i class="fa fa-weibo text-md"></i> 搜索引擎', 'filterd_sites' => $sites['site_search']])

    @include('sites.partials.sites', ['heading_title' => '<i class="fa fa-globe text-md"></i> 社区', 'filterd_sites' => $sites['site_community']])

    @include('sites.partials.sites', ['heading_title' => '<i class="fa fa-flag text-md"></i> 推荐博客', 'filterd_sites' => $sites['blog']])

    @include('sites.partials.sites', ['heading_title' => '<i class="fa fa-cloud text-md"></i> 技术', 'filterd_sites' => $sites['dev_technology']])

    @include('sites.partials.sites', ['heading_title' => '<i class="fa fa-cloud text-md"></i> 其他', 'filterd_sites' => $sites['site_other']])

    @include('sites.partials.sites', ['heading_title' => '<i class="fa fa-cloud text-md"></i> 购物', 'filterd_sites' => $sites['site_shop']])

</div>

{{--<div class="panel panel-default list-panel home-topic-list">
  <div class="panel-heading">
    <h3 class="panel-title text-center">
      {{ lang('Excellent Topics') }} &nbsp;
      <a href="{{ route('feed') }}" style="color: #E5974E; font-size: 14px;" target="_blank">
         <i class="fa fa-rss"></i>
      </a>
    </h3>

  </div>

  <div class="panel-body ">
	@include('pages.partials.topics')
  </div>

  <div class="panel-footer text-right">

  	<a href="topics?filter=excellent" class="more-excellent-topic-link">
  		{{ lang('More Excellent Topics') }} <i class="fa fa-arrow-right" aria-hidden="true"></i>
  	</a>
  </div>
</div>--}}

@stop
