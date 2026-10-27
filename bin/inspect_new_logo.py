from PIL import Image
import numpy as np

src_path = r"C:\Users\alima\.gemini\antigravity\brain\bfe5918e-cb25-4831-80a9-80fed2d87749\.user_uploaded\media_1793087877833_3da00a06.png"
img = Image.open(src_path)
print("Mode:", img.mode, "Size:", img.size)

arr = np.array(img)
print("Shape:", arr.shape)
print("Corner pixel (0,0):", arr[0,0])
