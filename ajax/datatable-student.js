function initializeDataTable(tableId, action, pageValue = null) {
  return $(tableId).DataTable({
    scrollY: "100vh",
    fixedHeader: true,
    displayStart: pageValue,
    responsive: true,
    dom: "Bfrtip",
    processing: true,
    serverSide: true,
    searching: true,
    bJQueryUI: true,
    pageLength: 50,
    order: [0, "desc"],
    columnDefs: getColumnDefs(),
    buttons: getButtons(),
    ajax: getAjaxConfig(action),
    columns: getColumns(),
    drawCallback: setupDrawCallback,
  });

  // $.ajax({
  //   url: '../php/controller/datatableController.php',
  //   type: 'POST',
  //   data: {
  //     action: 'fetchStudents',
  //     data : action
  //   },
  //   success: function (response) {
  //     console.log(response)
  //   },
  // })

}

function getColumns(spacial) {
  const baseColumns = [
    { data: "id" },
    { render: renderEnrollmentColumn },
    { data: "student_name" },
    { data: "father_name" },
    { data: "course_code" },
    { data: "branch_name" },
    { render: renderDateAdmissionColumn },
    { render: renderApprovalColumn },
    { render: renderButtonGroupColumn },
    { render: renderStudentStatusColumn },
  ];

  if (spacial === "expiry_date") {
    baseColumns.push({ render: renderExpiryDateColumn });
  }

  return baseColumns;
}

function getColumnDefs() {
  return [
    { targets: [3, 6], visible: false },
    { targets: [7, 8, 9], orderable: false },
  ];
}

function renderButtonGroupColumn(data, type, row) {
  return `<div class="btn-group btn-group-sm">
    <a class="btn btn-success" href="edit-student.php?id=${row.id}">
      <i class="font-size-10 fas fa-user-edit"></i>
    </a>
    <button class="btn btn-danger delete" data-id="${row.id}">
      <i class="font-size-10 far fa-trash-alt"></i>
    </button>
    <a class="btn btn-info" href="details-student.php?id=${row.id}">
      <i class="font-size-10 fas fa-eye"></i>
    </a>
    <button class="btn btn-primary cartificate" onclick="edit('${row.enrollment}')">
      <i class="font-size-10 fas fa-certificate
      "></i>
    </button>
  </div>`;
}

function setupDrawCallback(settings) {
  const classes = {
    complete: "btn-soft-success w-100 pe-3 text-start",
    running: "btn-soft-info w-100 pe-3 text-start",
    dropout: "btn-soft-danger w-100 pe-3 text-start",
  };

  initializeCustomSelect2(".select2", classes);

  $(".approve").click(function () {
    processApproval($(this));
  });

  $(".student_status").on("change", function () {
    const id = $(this).data("id");
    const value = $(this).val();
    const classes = {
      complete: "text-success",
      running: "text-info",
      dropout: "text-danger",
    };
    console.log(id);

    var currentClasses = Object.values(classes).join(" ");
    if ($(this).hasClass(currentClasses)) {
      $(this).removeClass(currentClasses);
    }
    var newClass = classes[value];
    if (newClass) {
      $(this).addClass(newClass);
      $(this).children().prop("disabled", false);
      $(this).find(":selected").prop("disabled", true);
    }
    const data = {
      id: id,
      status: value,
      action: "updateStudentStatus",
    };

    const success = function (response) {
      console.log(response);
      if (response.status === true) {
        const classes = {
          complete: " bg-success text-light",
          running: " bg-info text-light",
          dropout: " bg-danger text-light",
        };
        const currentClasses = classes[response.color] ?? "success";
        alertify.set("notifier", "position", "top-right");
        alertify.notify(response.message, currentClasses, 3);
      }
    };
    const url = "../php/controller/studentController.php";
    performAjaxRequest(url, data, success);
  });
}

function processApproval($element) {
  const itemId = $element.data("id");
  const data = {
    itemId: itemId,
    action: "approveStatusUpdate",
  };

  Swal.fire({
    title: "Processing: Please Approve Student",
    html: '<button class="btn btn-sm btn-success">This process is in progress.</button>',
    didOpen: function () {
      Swal.showLoading();
      timerInterval = setInterval(function () {
        var content = Swal.getHtmlContainer();
        if (content) {
          var b = content.querySelector("b");
          if (b) {
            b.textContent = Swal.getTimerLeft();
          }
        }
      }, 100);
    },
  });

  const success = function (response) {
    Swal.close();
    console.log(response);
    if (response.status === "success") {
      $("#row" + itemId).text(response.enrollment);
      const $icon = $element.find("i");
      $element.removeClass("bg-danger").addClass("bg-success");
      $icon.removeClass("bx-x").addClass("bx-badge-check");
    } else {
      alert(response.message);
    }
  };

  const url = "../php/controller/studentController.php";
  performAjaxRequest(url, data, success);
}