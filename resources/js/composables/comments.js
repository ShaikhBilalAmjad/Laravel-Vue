import { ref, inject } from 'vue'
import { useRouter } from 'vue-router'

export default function useComments() {
    const comments = ref({})
    const comment = ref({
        message: ''
    })
    const router = useRouter()
    const validationErrors = ref({})
    const isLoading = ref(false)
    const swal = inject('$swal')

    const getComments = async (
        page = 1,
    ) => {
        axios.get('/api/comments?page=' + page )
            .then(response => {
                comments.value = response.data;
            })
    }

    const getComment = async (id) => {
        axios.get('/api/comments/' + id)
            .then(response => {
                comment.value = response.data.data;
            })
    }

    const storeComment = async (comment) => {
        if (isLoading.value) return;

        isLoading.value = true
        validationErrors.value = {}

        let serializedComment = new FormData()
        for (let item in comment) {
            if (comment.hasOwnProperty(item)) {
                serializedComment.append(item, comment[item])
            }
        }

        axios.post('/api/comments', serializedComment,{
            headers: {
                "content-type": "multipart/form-data"
            }
        })
            .then(response => {
                router.push({name: 'comments.index'})
                swal({
                    icon: 'success',
                    title: 'Comment saved successfully'
                })
            })
            .catch(error => {
                if (error.response?.data) {
                    validationErrors.value = error.response.data.errors
                }
            })
            .finally(() => isLoading.value = false)
    }

    const updateComment = async (comment) => {
        if (isLoading.value) return;

        isLoading.value = true
        validationErrors.value = {}

        axios.put('/api/comments/' + comment.id, comment)
            .then(response => {
                router.push({name: 'comments.index'})
                swal({
                    icon: 'success',
                    title: 'Comment updated successfully'
                })
            })
            .catch(error => {
                if (error.response?.data) {
                    validationErrors.value = error.response.data.errors
                }
            })
            .finally(() => isLoading.value = false)
    }

    const deleteComment = async (id) => {
        swal({
            title: 'Are you sure?',
            text: 'You won\'t be able to revert this action!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            confirmButtonColor: '#ef4444',
            timer: 20000,
            timerProgressBar: true,
            reverseButtons: true
        })
            .then(result => {
                if (result.isConfirmed) {
                    axios.delete('/api/comments/' + id)
                        .then(response => {
                            getComments()
                            router.push({name: 'comments.index'})
                            swal({
                                icon: 'success',
                                title: 'Comment deleted successfully'
                            })
                        })
                        .catch(error => {
                            swal({
                                icon: 'error',
                                title: 'Something went wrong'
                            })
                        })
                }
            })

    }

    return {
        comments,
        comment,
        getComments,
        getComment,
        storeComment,
        updateComment,
        deleteComment,
        validationErrors,
        isLoading
    }
}
