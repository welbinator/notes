jQuery((function(e){var t,n=0;e(".notes-submit-btn").click((function(a){a.preventDefault();try{t.onreadystatechange=null,t.abort()}catch(a){}var o=e(this).parents("form").attr("id"),s=e("#llms_note_text").val(),l={action:"student_notes_ajax",title:e("#"+o+" input[name=llms_note_title]").val(),note:s="learndash-notes-form-popup"==o?tinymce.editors.llms_note_text_popup.getContent():tinymce.editors.llms_note_text.getContent(),llms_notify_admin:e("#"+o+" input[name=llms_notify_admin]").is(":checked"),related_post_id:e("#"+o+" input[name=related_post_id]").val(),related_post_type:e("#"+o+" input[name=related_post_type]").val(),related_user_id:e("#"+o+" input[name=related_user_id]").val(),ld_student_notes_add_note_nonce:e("#"+o+" #ld_student_notes_add_note_nonce").val()};s||e(this).effect("shake"),s&&(n=e("#"+o+" .student-notes-layer")).slideDown(),t=jQuery.ajax({method:"POST",url:sn_object.ajax_url,data:l,complete:function(e,t){n.slideUp()},success:function(t){var n=e.parseJSON(""+t);e("#"+o)[0].reset(),e("#"+o+" input[name=llms_note_title]").val(""),n.note_id&&(e("#"+o).append(n.success_msg),e(".all-my-post-notes .accordian-content").html(n.list)),e("#"+o+" .alert-success").delay(5e3).slideUp("slow",(function(){}))},error:function(e,t,n){console.error(JSON.parse(e.responseText))}})})),e(".all-notes-container").on("click","a.del-note",(function(a){a.preventDefault();try{t.onreadystatechange=null,t.abort()}catch(a){}var o=0;o=e(this);var s=e(this).data("note-id");if(s){(n=e(this).parents("#accordion-Historical").find(".student-notes-layer")).slideDown();let a=e("#ld_student_notes_delete_note_"+s+"_nonce").val();var l={action:"student_notes_ajax_del",note_id:s};l["ld_student_notes_delete_note_"+s+"_nonce"]=a,t=jQuery.ajax({method:"POST",url:sn_object.ajax_url,data:l,complete:function(e,t){n.slideUp()},success:function(t){var n=e.parseJSON(""+t);o.html("deleted..."),o.parent().parent().html(n.success_msg),e(".alert-danger").delay(5e3).slideUp("slow",(function(){e(this).parent().slideUp()}))},error:function(e,t,n){console.error(JSON.parse(e.responseText))}})}})),e(".all-notes-container").on("click","a.mark-read",(function(n){n.preventDefault();try{t.onreadystatechange=null,t.abort()}catch(n){}var a=e(this).data("note-id");if(a){let n=e("#ld_student_notes_read_note_"+a+"_nonce").val();var o={action:"student_notes_ajax_mark_read",note_id:a};o["ld_student_notes_read_note_"+a+"_nonce"]=n,t=jQuery.ajax({method:"POST",url:sn_object.ajax_url,data:o,success:function(e){location.reload()},error:function(e,t,n){console.error(JSON.parse(e.responseText))}})}}))}));