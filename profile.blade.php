<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
?>

@extends('adminlte::page')

@section('title', 'GEWISS - Gatepass | Dolgozói profil')

@section('content_header')

<?php

use App\Models\Contractor;
use App\Models\Subcontractor;

$in = 0;
$sum = 0;

$contractors = Contractor::all();
$subcontractors = Subcontractor::all();
$contractor_response = '';
foreach ($contractors as $contractor) {
    $contractor_response .= '<option value="' . $contractor->id . '">' . $contractor->name . '</option>';
}
$subcontractor_response = '';
foreach ($subcontractors as $subcontractor) {
    $subcontractor_response .= '<option value="' . $subcontractor->id . '">' . $subcontractor->name . '</option>';
}

?>


<h1 class="m-0 text-dark"><i class="fas fa-user mr-1"></i> Profil</h1>
@stop

@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <!-- Profile Image -->
            <div class="card card-warning card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img width="100px" @if(!isset($employee->photo)) class="img-circle" src="<?php echo url('/') ?>/storage/logo.webp" @else class="img-circle avatar-image employee-image" data-id="{{$employee->id}}" src="{{$employee->photo}}" @endif  id="photo" alt="profilkép">
                    </div>
                    <h3 class="profile-username text-center" value="{{$employee->fullname}}" id="name">{{$employee->fullname}}</h3>
                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Regisztráció</b> <a class="float-right" style="color: #ffc107">{{date('Y.m.d.', strtotime($employee->created_at))}}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Státusz</b> <a class="float-right">@if($employee->status == 1) <span class="badge badge-success float-right">Aktív</span> @else <span class="badge badge-danger float-right">Inaktív</span> @endif </a>
                        </li>
                        <li class="list-group-item"><b>Dolgozó kódja</b>
                            <a class="float-right" value="{{$employee->employee_code}}">{{$employee->employee_code}}</a>
                        </li>
                    </ul>
                    @if($employee->status == 0)
                    <button class="btn btn-success btn-block btn-activate" data-id="{{$employee->id}}">
                        <b><i class="fas fa-unlock"></i> Feloldás </b>
                    </button>
                    @else
                    <button class="btn btn-danger btn-block btn-deactivate" data-id="{{$employee->id}}">
                        <b><i class="fas fa-ban"></i> Tiltás </b>
                    </button>
                    @if($employee->has_card == 0)
                    <button class="btn btn-warning btn-block btn-addcard" data-id="{{$employee->id}}">
                        <b><i class="fas fa-id-card"></i> Kártya kiadása </b>
                    </button>
                    @else
                    <button class="btn btn-warning btn-block btn-lostcard" data-id="{{$employee->id}}" data-employee_code="{{$employee->employee_code}}">
                        <b><i class="fas fa-id-card"></i> Elhagyott kártya</b>
                    </button>
                    @endif
                    @endif
                    @if(Auth::user()->can_print > 0)
                    <?php $printCode = '^XA^FO250,70^BY2^BCN,120,Y,N,N,A^FD' . $employee->employee_code . '^FS^XZ'; ?>
                    <button class="btn btn-primary btn-block" onclick="writeToSelectedPrinter('<?php echo $printCode ?>')"><b><i class="fas fa-print"></i> Címkenyomtatás</b></button>
                    @endif
                    @if(Auth::user()->can_print > 0)
                    <button class="btn btn-success btn-block" onclick="generateCard()"><b><i class="fas fa-print"></i> Kártyanyomtatás</b></button>
                    @endif
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->

            <!-- About Me Box -->
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><strong>Információk</strong></h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">

                    <strong> Fővállalkozó</strong><br>
                    <span class="text-muted" id="id"><i class="fas fa-building"></i>@if(isset($employee->contractor)) {{$employee->contractor}} @else Nincs megadva @endif</span><br>
                    <span class="text-muted"><i class="fas fa-user-tie"></i>@if(isset($employee->contractor_contact)) {{$employee->contractor_contact}} @else Nincs megadva @endif</span><br>
                    <span class="text-muted"><i class="fas fa-phone"></i>@if(isset($employee->contractor_phone)) {{$employee->contractor_phone}} @else Nincs megadva @endif</span><br><br>

                    <strong> Alvállalkozó</strong><br>
                    <span class="text-muted"><i class="fas fa-building"></i>@if(isset($employee->subcontractor)) {{$employee->subcontractor}} @else Nincs megadva @endif</span><br>
                    <span class="text-muted"><i class="fas fa-user-tie"></i>@if(isset($employee->subcontractor_contact)) {{$employee->subcontractor_contact}} @else Nincs megadva @endif</span><br>
                    <span class="text-muted"><i class="fas fa-phone"></i>@if(isset($employee->subcontractor_phone)) {{$employee->subcontractor_phone}} @else Nincs megadva @endif</span><br>
                    <br>
                    <li class="list-group-item"><b>Személyi</b>
                        <a class="float-right">{{$employee->idcard}}</a>
                    </li>
                    <br>
                    @if(strtotime('now') < strtotime($employee->created_at) + 900 && Auth::user()->role == 0 )
                        <button class="btn btn-warning btn-block btn-employee-edit" data-id="{{$employee->id}}">
                            <b><i class="fas fa-edit"></i> Adatmódosítás </b>
                        </button>
                        @elseif(Auth::user()->role > 0)
                        <button class="btn btn-warning btn-block btn-employee-edit" data-id="{{$employee->id}}">
                            <b><i class="fas fa-edit"></i> Adatmódosítás </b>
                        </button>
                        @endif
                        <button class="btn btn-info btn-block btn-imgupload" data-id="{{$employee->id}}">
                            <b><i class="fas fa-image"></i> Új fotó készítése</b>
                        </button>
                        @if(isset($employee->photo))
                        <button class="btn btn-danger btn-block btn-imgdelete" data-id="{{$employee->id}}">
                            <b><i class="fas fa-image"></i> Fotó törlése</b>
                        </button>
                        @endif
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
        <div class="col-9">
            <div class="card card-warning card-outline">
                @if(Auth::user()->role == 2)
                <div class="card-header">
                    <form method="GET" action="{{ route('employees.show', $employee->id) }}">
                        <div class="row">
                            <input type="hidden" name="_token" value="{{csrf_token()}}">
                            <input style="margin: 0px 5px;" type="month" name="month" required="required" value="{{$month}}" min="2022-06" max="{{date('Y-m', strtotime(now()))}}" placeholder="Dátum"></input>
                            <button type="submit" class="btn btn-warning"><i class="fas fa-filter"></i> Hónap kiválasztása</button>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <table id="table" class="table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">Munkanap</th>
                                <th class="text-center">Belépés ideje</th>
                                <th class="text-center">Kilépés ideje</th>
                                <th class="text-center">Munkaterületen töltött idő</th>
                                <th class="text-center">Távozás</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($worktime['attendance'] as $attendance)
                            <tr>
                                <td class="text-center"> {{date('Y.m.d.', strtotime($attendance['day']))}} </td>
                                <td class="text-center"> {{date('Y.m.d. H:i:s', strtotime($attendance['enter']))}}</td>
                                <td class="text-center"> {{date('Y.m.d. H:i:s', strtotime($attendance['exit']))}}</td>
                                <td class="text-center"> {{$attendance['time']}}</td>
                                <td class="text-center"> @if($attendance['warning'] == 0) <span class="badge badge-success rounded-pill"> OK </span> @else <span class="badge badge-warning rounded-pill"> NOK </span> @endif</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <span>
                        <h5>A beállított időszakra vonatkozó, munkterületen eltöltött összes idő: <strong>{{$worktime['sum']}}</strong> óra</h5>
                    </span>
                </div>
                @else
                Nincs jogosultsága a megtekintéshez.
                @endif
            </div>
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->
</div><!-- /.container-fluid -->
@endsection

@push('js')


<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript" src="//cdn.datatables.net/plug-ins/1.12.1/i18n/hu.json"></script>

<script>
    var contractors = <?php echo json_encode($contractor_response); ?>;
    var subcontractors = <?php echo json_encode($subcontractor_response); ?>;
    var imguploadPath = "{{route('employees.imageuploadAjax')}}";
    var imgdeletePath = "{{route('employees.imagedeleteAjax')}}";
    var getattributesPath = "{{route('employees.getattributesAjax')}}";
    var updatePath = "{{route('employees.updateAjax')}}";
    var enterPath = "{{route('employees.enterAjax')}}";
    var exitPath = "{{route('employees.exitAjax')}}";
    var activatePath = "{{route('employees.activateAjax')}}";
    var deactivatePath = "{{route('employees.deactivateAjax')}}";
    var addcardPath = "{{route('employees.addcardAjax')}}";
    var lostcardPath = "{{route('employees.lostcardAjax')}}";
    var csrf = "{{ csrf_token() }}";
</script>

<script src="{{ asset('js/components/employee/imagedelete.js') }}"></script>
<script src="{{ asset('js/components/employee/imageupload.js') }}"></script>
<script src="{{ asset('js/components/employee/showimage.js') }}"></script>
<script src="{{ asset('js/components/employee/update.js') }}"></script>
<script src="{{ asset('js/components/employee/destroy.js') }}"></script>
<script src="{{ asset('js/components/employee/enter.js') }}"></script>
<script src="{{ asset('js/components/employee/exit.js') }}"></script>
<script src="{{ asset('js/components/employee/activate.js') }}"></script>
<script src="{{ asset('js/components/employee/deactivate.js') }}"></script>
<script src="{{ asset('js/components/employee/addcard.js') }}"></script>
<script src="{{ asset('js/components/employee/lostcard.js') }}"></script>
<script src="{{ asset('js/components/mobileTable.js') }}"></script>
<script>
    $(window).on('load', function() {
        $("#cover").fadeOut(500);
    });
</script>

<script src="https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
  <script>

    async function fileToDataURL(fileInput) {
      const file = fileInput.files[0];
      return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = reject;
        reader.readAsDataURL(file);
      });
    }

    function getImageDataURLFromImgTag(imgElement) {
      const canvas = document.createElement('canvas');
      canvas.width = imgElement.naturalWidth;
      canvas.height = imgElement.naturalHeight;
      const ctx = canvas.getContext('2d');
      ctx.drawImage(imgElement, 0, 0);
      return canvas.toDataURL('image/jpeg'); // Or 'image/png' if needed
    }

    async function fetchImageAsDataURL(imageUrl) {
    const res = await fetch(imageUrl);
    const blob = await res.blob();

    return await new Promise((resolve) => {
        const reader = new FileReader();
        reader.onloadend = () => resolve(reader.result);
        reader.readAsDataURL(blob);
    });
    }

    async function generateCard() {
      const { PDFDocument, rgb, StandardFonts } = PDFLib;
      const name = document.getElementById('name')?.innerText || 'Név ismeretlen';
      const id = document.getElementById('id')?.innerText || 'Azonosító ismeretlen';
      const bg = '<?php echo url('/') ?>/storage/bg.jpg';
      console.log(name)
      console.log(id)
      console.log(document.getElementById('photo'))
      const profileDataUrl = getImageDataURLFromImgTag(document.getElementById('photo'));
      const backgroundDataUrl = await fetchImageAsDataURL(bg);
      const pdfDoc = await PDFDocument.create();
      const page = pdfDoc.addPage([637, 1012]); // CR-80 card dimensions
      const backgroundImage = await pdfDoc.embedJpg(backgroundDataUrl);
        page.drawImage(backgroundImage, {
            x: 0,
            y: 0,
            width: 649,
            height: 1012
        });

        const profileImage = await pdfDoc.embedJpg(profileDataUrl);

        const profileWidth = 300;
        const profileHeight = 300;
        const profileX = (page.getWidth() - profileWidth) / 2;

        page.drawImage(profileImage, {
        x: profileX,
        y: page.getHeight() - profileHeight - 350, // Adjust the Y position to fit the card
        width: profileWidth,
        height: profileHeight
        });


      const nameSize = 40;
      const idSize = 35;
      const font = await pdfDoc.embedFont(StandardFonts.HelveticaBold);
      const nameWidth = font.widthOfTextAtSize(name, nameSize);
      const idWidth = font.widthOfTextAtSize(id, idSize);

    page.drawText(name, {
    x: (page.getWidth() - nameWidth) / 2,
    y: 250,
    size: nameSize,
    font,
    color: rgb(1, 1, 1)
    });

    page.drawText(id, {
    x: (page.getWidth() - idWidth) / 2,
    y: 200,
    size: idSize,
    font,
    color: rgb(1, 1, 1)
    });

      const pdfBytes = await pdfDoc.save();
      const pdfBlob = new Blob([pdfBytes], { type: 'application/pdf' });

        const formData = new FormData();
        formData.append('file', pdfBlob, 'card.pdf');

        fetch('http://localhost:3001/print', {
        method: 'POST',
        body: formData
        })
        .then(res => res.json())
        .then(data => console.log('Print result:', data))
        .catch(err => console.error('Print error:', err));

    }
  </script>

@endpush