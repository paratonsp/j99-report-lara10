<div class="user-panel mt-3 pb-3 mb-3 d-flex">
    <div class="image">
        <img src="{{asset('assets/images/user-avatar.png')}}" class="img-circle elevation-2 bg-light" alt="User Image">
    </div>
    <div class="info">
        <a href="#" class="d-block">{{ auth()->user()->name }}</a>
    </div>
</div>