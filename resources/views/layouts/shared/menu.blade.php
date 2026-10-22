<nav class="mt-2">
  <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
    @foreach (Session('menu_session') as $menu)
    @if (!isset($menu->child))
    <li class="nav-item {{ (request()->segment(1) ==  $menu->url) ? 'menu-open' : '' }}">
      <a href="{{ url($menu->url) }}" class="nav-link {{ (request()->segment(1) == $menu->url) ? 'active' : '' }}">
        <i class="{{ $menu->icon }}"></i>
        <p>{{ $menu->title }}</p>
      </a>
    </li>
    @else
    <li class="nav-item {{ (request()->segment(1) == $menu->url) ? 'menu-open' : '' }}">
      <a href="#" class="nav-link  {{ (request()->segment(1) == $menu->url) ? 'active' : '' }}">
        <i class="{{ $menu->icon }}"></i>
        <p>
          {{ $menu->title }}
          <i class="right fas fa-angle-left"></i>
        </p>
      </a>
      <ul class="nav nav-treeview">

        @foreach ($menu->child as $child)
        @php
        $segmentCheck = (isset(explode("/", $child->url)[1]))
        ? (request()->segment(2) === explode("/", $child->url)[1])
        : (request()->segment(1) === $child->url);
        @endphp
        <li class="nav-item">
          <a href="{{ url($child->url) }}" class="nav-link {{ $segmentCheck ? 'active' : '' }}">
            <i class="{{ $child->icon }}"></i>
            <p>{{ $child->title }}</p>
          </a>
        </li>
        @endforeach
      </ul>
    </li>
    @endif
    @endforeach
    <li class="nav-item">
      <a href="{{ route('reports.addons', ['report' => 'insurance']) }}" class="nav-link {{ request()->is('reports/insurance*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-shield-alt"></i><p>Insurance Report</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('reports.addons', ['report' => 'flexi']) }}" class="nav-link {{ request()->is('reports/flexi*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-ticket-alt"></i><p>Flexi Tiket Report</p>
      </a>
    </li>
  </ul>
</nav>
