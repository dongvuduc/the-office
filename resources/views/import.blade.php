<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {font-family: Arial, Helvetica, sans-serif;width: 700px;margin: auto;}
        form {border: 3px solid #f1f1f1;text-align: center}

        input {
            padding: 15px;
            border: 1px solid #cdcdcd;
            border-radius: 5px;
            width: 80%;
            margin-top: 10px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 14px 20px;
            margin: 8px 0;
            border: none;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            opacity: 0.8;
        }

        .imgcontainer {
            text-align: center;
            margin: 24px 0 12px 0;
        }

        img.avatar {
            width: 100px;
        }

        .container {
            padding: 16px;
        }

        span.psw {
            float: right;
            padding-top: 16px;
        }
        .error{
            color: red;
        }
        .alert{
            color: green;
        }

    </style>
</head>
<body>

<h2>Hi, {{$user->email}}</h2>

<form action="{{route('import')}}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="imgcontainer">
        <img src="{{asset('image/excel.ico')}}" class="avatar">
    </div>
    <h4>Import Transaction Data</h4>
    <p>Download sample data <a href="{{asset('import/import.xlsx')}}">Excel</a> or <a href="{{asset('import/import.csv')}}">CSV</a></p>
    @if(session()->has('message'))
        <div class="alert">
            {{ session()->get('message') }}
            <p><a href="{{env('SERVER_URL')}}/home">View Result</a></p>
        </div>
    @endif
    <input id="fileSelect" required name="import_file" type="file" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" />
    @error('import_file')
    <div class="error">{{ $message }}</div>
    @enderror

    @if (session()->has('error'))
        <div class="alert alert-danger">
            <ul>
                @foreach (session()->get('error') as $error)
                    @foreach ($error->errors as $errors)
                        <li>There was an error on row {{$error->row}}. {{$errors}}</li>
                    @endforeach
                @endforeach
            </ul>
        </div>
    @endif

    <div class="container">
        <button type="submit">Submit</button>
    </div>
</form>

</body>
</html>