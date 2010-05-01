# This file is auto-generated from the current state of the database. Instead of editing this file, 
# please use the migrations feature of Active Record to incrementally modify your database, and
# then regenerate this schema definition.
#
# Note that this schema.rb definition is the authoritative source for your database schema. If you need
# to create the application database on another system, you should be using db:schema:load, not running
# all the migrations from scratch. The latter is a flawed and unsustainable approach (the more migrations
# you'll amass, the slower it'll run and the greater likelihood for issues).
#
# It's strongly recommended to check this file into your version control system.

ActiveRecord::Schema.define(:version => 20100501131638) do

  create_table "discs", :force => true do |t|
    t.text     "comments",                      :null => false
    t.string   "internal_date",   :limit => 10, :null => false
    t.string   "internal_serial",               :null => false
    t.integer  "status",          :limit => 1,  :null => false
    t.string   "title",                         :null => false
    t.string   "subtitle",                      :null => false
    t.string   "scene_filename",                :null => false
    t.string   "filename",                      :null => false
    t.integer  "size"
    t.string   "crc32",           :limit => 8,  :null => false
    t.string   "md5",             :limit => 32, :null => false
    t.string   "sha1",            :limit => 40, :null => false
    t.datetime "created_at"
    t.datetime "updated_at"
  end

end
