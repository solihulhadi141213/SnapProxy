<!-- Tools -->
<div class="accordion-item">
    <h2 class="accordion-header" id="headingAuthor">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAuthor" aria-expanded="false" aria-controls="collapseAuthor">
            <b>Tools</b>
        </button>
    </h2>
    <div id="collapseAuthor" class="accordion-collapse collapse" aria-labelledby="headingAuthor" data-bs-parent="#accordionDocs">
        <div class="accordion-body">
            <div class="row">
                <div class="col-md-6">
                    <form action="javascript:void(0);" id="ProsesGenerateRandomString">
                        <div class="card custom-card">
                            <div class="card-header">
                                <i class="bi bi-file-earmark-font"></i> Random String Generator
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <small>Buat string dengan karakter acak sebanyak 1-36 karakter.</small>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-4">
                                        <label for="string_count" class="form-label">
                                            <small>Jumlah Karakter</small>
                                        </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="number" min="1" max="36" name="string_count" id="string_count" class="form-control" />
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-4">
                                        <label for="random_string" class="form-label">
                                            <small>Random String</small>
                                        </label>
                                    </div>
                                    <div class="col-8">
                                        <div class="input-group">
                                            <textarea readonly name="random_string" id="random_string" class="form-control"></textarea>
                                            <button type="button" id="CopyString" class="btn btn-outline-secondary" title="Copy">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-md btn-secondary">
                                    <i class="bi bi-arrow-right-circle"></i> Generate
                                </button>
                                <button type="reset" class="btn btn-md btn-dark">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-6">
                    <form action="javascript:void(0);" id="ProsesHashGenerator">
                        <div class="card custom-card">
                            <div class="card-header">
                                <i class="bi bi-file-earmark-font"></i> Hash Generator
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <small>Ubah string menjadi <code class="text text-secondary">bcrypt hash</code> dengan algoritma <code class="text text-secondary">PASSWORD_DEFAULT</code></small>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-4">
                                        <label for="string_count" class="form-label">
                                            <small>String Asli</small>
                                        </label>
                                    </div>
                                    <div class="col-8">
                                        <textarea name="string_asli" id="string_asli" class="form-control"></textarea>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-4">
                                        <label for="string_hash" class="form-label">
                                            <small>String Hash</small>
                                        </label>
                                    </div>
                                    <div class="col-8">
                                        <div class="input-group">
                                            <textarea readonly name="string_hash" id="string_hash" class="form-control"></textarea>
                                            <button type="button" id="CopyStringHash" class="btn btn-outline-secondary" title="Copy">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-md btn-secondary">
                                    <i class="bi bi-arrow-right-circle"></i> Generate
                                </button>
                                <button type="reset" class="btn btn-md btn-dark">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>