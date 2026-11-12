<?php
/**
 * UI muat naik gambar kerosakan (3 slot, Kamera + Galeri)
 * $foto_saved: array 3 nilai filename sedia ada (optional)
 */
$foto_saved = $foto_saved ?? ['', '', ''];
?>
<div class="ea-sec-hdr mb-2"><i class="bi bi-camera"></i> <?php echo e($foto_section_title ?? 'Gambar Kerosakan'); ?>
    <small class="fw-normal text-muted" style="font-size:0.8rem;">(pilihan &bull; maks. 3 gambar &bull; 5MB setiap satu)</small>
</div>
<div class="mb-3">
    <p class="text-muted mb-2" style="font-size:0.85rem;"><i class="bi bi-info-circle"></i> Ambil gambar dengan kamera atau pilih dari galeri.</p>
    <div class="row g-2" id="foto-preview-row">
        <?php for ($si = 0; $si < 3; $si++): ?>
        <div class="col-4">
            <div class="foto-slot border rounded d-flex flex-column align-items-center justify-content-center bg-light position-relative"
                 style="height:110px;" id="slot-<?php echo $si; ?>">
                <i class="bi bi-camera" style="font-size:2rem;color:#aaa;" id="icon-<?php echo $si; ?>"></i>
                <small class="text-muted mt-1" id="lbl-<?php echo $si; ?>">Gambar <?php echo $si + 1; ?></small>
                <img id="preview-<?php echo $si; ?>" src="" alt=""
                     class="d-none position-absolute top-0 start-0 w-100 h-100"
                     style="object-fit:cover;border-radius:0.375rem;">
                <button type="button"
                        class="btn btn-sm btn-danger position-absolute top-0 end-0 d-none"
                        id="remove-<?php echo $si; ?>"
                        style="padding:1px 6px;font-size:0.7rem;margin:3px;z-index:5;"
                        onclick="eaRemoveFoto(event,<?php echo $si; ?>)">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="d-flex gap-1 mt-1">
                <button type="button" class="btn btn-outline-secondary btn-sm flex-fill py-0"
                        style="font-size:0.72rem;" onclick="eaTriggerFotoCam(<?php echo $si; ?>)">
                    <i class="bi bi-camera-fill"></i> Kamera
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm flex-fill py-0"
                        style="font-size:0.72rem;" onclick="eaTriggerFotoGal(<?php echo $si; ?>)">
                    <i class="bi bi-images"></i> Galeri
                </button>
            </div>
            <input type="file" id="file-cam-<?php echo $si; ?>" accept="image/*" capture="environment"
                   class="d-none" onchange="eaHandleFoto(<?php echo $si; ?>, 'cam')">
            <input type="file" id="file-gal-<?php echo $si; ?>" accept="image/*"
                   class="d-none" onchange="eaHandleFoto(<?php echo $si; ?>, 'gal')">
            <input type="hidden" name="foto_<?php echo $si; ?>" id="hfoto_<?php echo $si; ?>"
                   value="<?php echo e($foto_saved[$si] ?? ''); ?>">
        </div>
        <?php endfor; ?>
    </div>
    <div id="foto-status" class="mt-2" style="font-size:0.82rem;min-height:1.2em;"></div>
</div>
