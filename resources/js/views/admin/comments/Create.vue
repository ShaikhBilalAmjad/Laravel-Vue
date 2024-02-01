<template>
    <form @submit.prevent="submitForm">
        <div class="row my-5">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body row">

                        <!-- Title -->
                        <div class="col-md-10">
                            <label for="comment-message" class="form-label">
                                Comment
                            </label>
                            <input v-model="comment.message" id="comment-message" type="text" class="form-control">
                            <input v-model="comment.post_id" type="hidden" name="post_id" value="">
                            <div class="text-danger mt-1">
                                {{ errors.message }}
                            </div>
                            <div class="text-danger mt-1">
                                <div v-for="message in validationErrors?.message">
                                    {{ message }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2" style="margin-top: 1.8rem !important; text-align:right;">
                            <button :disabled="isLoading" class="btn btn-primary">
                                <div v-show="isLoading" class=""></div>
                                <span v-if="isLoading">Processing...</span>
                                <span v-else>Publish</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>
<script setup>
import {onMounted, reactive, ref} from "vue";
import { useRoute } from "vue-router";
import DropZone from "@/components/DropZone.vue";
import useComments from "@/composables/comments";
import {useForm, useField, defineRule} from "vee-validate";
import {required, min} from "@/validation/rules"

defineRule('required', required)
defineRule('min', min);

const dropZoneActive = ref(true)
const route = useRoute()

// Define a validation schema
const schema = {
    message: 'required|min:3',
}
// Create a form context with the validation schema
const {validate, errors} = useForm({validationSchema: schema})
// Define actual fields for validation
const {value: message} = useField('message', null, {initialValue: ''});
const {value: post_id} = useField('post_id', null, {initialValue: route.params.id});
const {storeComment, validationErrors, isLoading} = useComments();
const comment = reactive({
    message,
    post_id
})

const thefile = ref('')

function submitForm() {
    validate().then(form => {
        if (form.valid) storeComment(comment)
    })
}

onMounted(() => {
    comment.post_id = route.params.postid
})


</script>
